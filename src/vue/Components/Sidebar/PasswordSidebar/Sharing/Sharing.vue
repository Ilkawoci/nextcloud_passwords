<template>
    <div class="sharing-container">
        <nc-note-card type="warning" :text="t('End-to-End encryption will be disabled for this password if you share it.')" v-if="hasCse && canBeShared"/>
        <nc-note-card type="info" class="shareby-info" v-if="isSharedWithUser">
            <template #icon>
                <nc-avatar disable-menu :size="32" :user="item.share.owner.id" :display-name="item.share.owner.name"/>
            </template>
            <span>
                <translate :say="isFolder ? '{name} has shared this folder with you.' : '{name} has shared this password with you.'" :variables="item.share.owner"/>
                <translate :say="item.share.editable ? 'ShareInfoEditable':'ShareInfoNotEditable'"/>
                <translate :say="item.share.shareable ? 'ShareInfoShareable':'ShareInfoNotShareable'"/>
                <translate say="It will expire {date}." :variables="expirationDate" v-if="hasExpirationDate"/>
            </span>
        </nc-note-card>

        <share-edit-form
                :share="activeShare"
                :password="password"
                :folder="folder"
                :default-preset="editingPreset"
                v-on:configuring="showShares = !$event"
                v-on:revoke="deleteShareEvent"
                v-on:save="shareSavedEvent"
                v-on:cancel="setActiveShare()"
                v-if="canBeShared"
        />

        <ul class="share-list" v-if="hasShares">
            <share-list-item
                    :share="share"
                    :password="password"
                    :folder="folder"
                    v-on:delete="deleteShareEvent"
                    v-on:edit="setActiveShare"
                    :data-share-id="share.id"
                    v-for="share in shares"
                    :key="share.id"/>
        </ul>
        <nc-empty-content
                :name="t('SharingNoShares')"
                :description="t('SharingNoSharesText')"
                v-else-if="isEmpty">
            <template #icon>
                <share-variant-icon/>
            </template>
        </nc-empty-content>
        <nc-loading-icon :size="64" v-else-if="isLoading"/>
    </div>
</template>

<script>
    import Field from '@vc/Field';
    import API from '@js/Helper/api';
    import Translate from '@vc/Translate';
    import ShareListItem from '@vc/Sidebar/PasswordSidebar/Sharing/ShareListItem';
    import LocalisationService from "@js/Services/LocalisationService";
    import LoggingService from "@js/Services/LoggingService";
    import {getCurrentUser} from '@nextcloud/auth';
    import BatchActionManager from "@js/Manager/BatchActionManager";
    import {subscribe, subscribeOnce, unsubscribe} from "@js/Helper/event-bus";
    import ShareEditForm from "@vc/Sidebar/PasswordSidebar/Sharing/ShareEditForm";
    import NcNoteCard from "@nc/NcNoteCard.js";
    import NcEmptyContent from "@nc/NcEmptyContent.js";
    import NcLoadingIcon from '@nc/NcLoadingIcon.js';
    import ShareVariantIcon from "@icon/ShareVariant.vue";
    import UtilityService from "@js/Services/UtilityService";
    import NcAvatar from "@nextcloud/vue/components/NcAvatar";

    export default {
        components: {
            Field,
            Translate,
            ShareEditForm,
            ShareListItem,
            NcAvatar,
            NcNoteCard,
            NcLoadingIcon,
            NcEmptyContent,
            ShareVariantIcon
        },

        props: {
            password: {
                type: Object
            },
            folder: {
                type   : Object,
                default: null
            }
        },

        data() {
            let item   = (this.folder !== null && this.folder !== undefined) ? this.folder:this.password;
            let shares = item.hasOwnProperty('shares') ? UtilityService.objectToArray(item.shares):[];

            return {
                shares,
                interval     : null,
                polling      : {interval: null, mode: null},
                cronPromise  : null,
                user         : getCurrentUser(),
                showShares   : true,
                activeShare  : null,
                editingPreset: null,
                loading      : shares.length === 0
            };
        },

        created() {
            this.reloadShares(false);
            this.startPolling();
            subscribe(this.updateEvent, this.processItemUpdate);
        },

        beforeDestroy() {
            this.stopPolling();
            unsubscribe(this.updateEvent, this.processItemUpdate);
        },

        computed: {
            item() {
                return (this.folder !== null && this.folder !== undefined) ? this.folder:this.password;
            },
            isFolder() {
                return this.folder !== null && this.folder !== undefined;
            },
            updateEvent() {
                return this.isFolder ? 'passwords:folder:updated':'passwords:password:updated';
            },
            hasCse() {
                if(this.isFolder) return false;
                return this.item.cseType !== 'none' && !this.item.shared;
            },
            hasShares() {
                return this.canBeShared && this.showShares && !this.loading && this.shares.length > 0;
            },
            isEmpty() {
                return this.canBeShared && this.showShares && !this.loading && this.shares.length === 0;
            },
            isLoading() {
                return this.canBeShared && this.showShares && this.loading;
            },
            canBeShared() {
                return this.item.hasOwnProperty('share') &&
                       (
                           this.item.share === null ||
                           (
                               typeof this.item.share !== 'string' &&
                               this.item.share.hasOwnProperty('shareable') &&
                               this.item.share.shareable
                           )
                       );
            },
            hasExpirationDate() {
                if(this.item.share !== null && typeof this.item.share !== 'string') {
                    return this.item.share.expires !== null;
                }

                return false;
            },
            expirationDate() {
                if(this.hasExpirationDate) {
                    return {
                        'date'    : LocalisationService.formatDate(this.item.share.expires),
                        'dateTime': LocalisationService.formatDateTime(this.item.share.expires)
                    };
                }

                return {'date': '', 'dateTime': ''};
            },
            isSharedWithUser() {
                return this.item.share && this.item.share.owner;
            }
        },

        methods: {
            shareSavedEvent() {
                this.setActiveShare(null);
                this.shares = [];
                this.reloadShares();
            },
            setActiveShare(value = null) {
                if(value === null) {
                    this.activeShare = null;
                    this.editingPreset = null;
                } else {
                    this.activeShare = value.share;
                    this.editingPreset = value.preset;
                }

                this.showShares = value !== null;
            },
            reloadShares(withLoading = true) {
                if(withLoading) this.loading = true;

                // Folders must include the model: a bare 'shares' response lacks
                // parent/cseType and crashes the client's _processFolder() recursion.
                let promise = this.isFolder
                    ? API.showFolder(this.item.id, 'model+shares')
                    : API.showPassword(this.item.id, 'shares');

                promise
                   .then((d) => {
                       this.shares = UtilityService.objectToArray(d.shares);
                       this.loading = false;
                   })
                   .catch(LoggingService.catch);
            },
            deleteShareEvent($event) {
                delete this.shares[$event.id];
                this.refreshShares();
            },
            async refreshShares() {
                await this.runCron()
                          .then((d) => { if(d.success) this.reloadShares(false);});

                this.startPolling();
                this.$forceUpdate();
            },
            startPolling(mode = 'fast') {
                if(this.polling.mode === mode) return;
                this.stopPolling();

                let time = mode === 'slow' ? 60000:5000;
                this.polling.interval = setInterval(() => { this.reloadShares(false); }, time);
            },
            stopPolling() {
                if(this.polling.interval !== null) {
                    clearInterval(this.polling.interval);
                    this.polling.interval = null;
                    this.polling.mode = null;
                }
            },
            runCron() {
                if(this.cronPromise === null) {
                    this.cronPromise = new Promise((resolve, reject) => {
                        API.runSharingCron()
                           .then((d) => {
                               this.cronPromise = null;
                               resolve(d);
                           })
                           .catch((e) => {
                               this.cronPromise = null;
                               LoggingService.error(e);
                               reject(e);
                           });
                    });
                }

                return this.cronPromise;
            },
            processItemUpdate(item) {
                if(item.id === this.item.id) {
                    if(BatchActionManager.isProcessingItems) {
                        subscribeOnce('passwords:batch-action:completed', () => {this.processItemUpdate(item);});
                        return;
                    }

                    if(this.item.hasOwnProperty('shares')) {
                        this.shares = UtilityService.objectToArray(this.item.shares);
                    } else {
                        this.refreshShares();
                    }
                }
            }
        },

        watch: {
            item(value) {
                this.shares = value.hasOwnProperty('shares') ? UtilityService.objectToArray(value.shares):[];
                this.reloadShares(this.shares.length === 0);
                this.$forceUpdate();
            },
            shares(shares) {
                for(let share of shares) {
                    if(share.updatePending) {
                        this.runCron();
                        this.startPolling();
                        return;
                    }
                }
                this.startPolling('slow');
            }
        }
    };
</script>

<style lang="scss">
.sharing-container {
    position       : relative;
    padding-bottom : 5rem;

    .shareby-info {
        margin-bottom : 1rem;
    }

    .share-list,
    .loading-icon,
    .empty-content {
        margin-top : 1rem;
    }
}
</style>