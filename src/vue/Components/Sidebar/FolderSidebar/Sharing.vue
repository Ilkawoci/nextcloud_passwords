<!--
  - @copyright 2026 Passwords App
  -
  - @author Marius David Wieschollek
  - @license AGPL-3.0
  -
  - This file is part of the Passwords App
  - created by Marius David Wieschollek.
  -->
<template>
    <div class="sharing-container">
        <nc-note-card type="info" class="shareby-info" v-if="isSharedWithUser">
            <template #icon>
                <nc-avatar disable-menu :size="32" :user="folder.share.owner.id" :display-name="folder.share.owner.name"/>
            </template>
            <span>
                <translate say="{name} has shared this folder with you." :variables="folder.share.owner"/>
                <translate :say="folder.share.editable ? 'You can edit this folder.':'You can not edit this folder.'"/>
                <translate :say="folder.share.shareable ? 'You can share this folder with others.':'You can not share this folder.'"/>
            </span>
        </nc-note-card>

        <share-edit-form
                :share="activeShare"
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
                    :folder="folder"
                    v-on:delete="deleteShareEvent"
                    v-on:edit="setActiveShare"
                    :data-share-id="share.id"
                    v-for="share in shares"
                    :key="share.id"/>
        </ul>
        <nc-empty-content
                :name="t('No shares yet')"
                :description="t('Shares for other users will appear here.')"
                v-else-if="isEmpty">
            <template #icon>
                <share-variant-icon/>
            </template>
        </nc-empty-content>
        <nc-loading-icon :size="64" v-else-if="isLoading"/>
    </div>
</template>

<script>
    import API from '@js/Helper/api';
    import Translate from '@vc/Translate';
    import ShareListItem from '@vc/Sidebar/PasswordSidebar/Sharing/ShareListItem';
    import ShareEditForm from '@vc/Sidebar/PasswordSidebar/Sharing/ShareEditForm';
    import LoggingService from "@js/Services/LoggingService";
    import UtilityService from "@js/Services/UtilityService";
    import NcNoteCard from "@nc/NcNoteCard.js";
    import NcEmptyContent from "@nc/NcEmptyContent.js";
    import NcLoadingIcon from "@nc/NcLoadingIcon.js";
    import NcAvatar from "@nextcloud/vue/components/NcAvatar";
    import ShareVariantIcon from "@icon/ShareVariant.vue";

    export default {
        components: {
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
            folder: {
                type: Object
            }
        },

        data() {
            let shares = this.folder.hasOwnProperty('shares') ? UtilityService.objectToArray(this.folder.shares):[];

            return {
                shares,
                polling      : {interval: null, mode: null},
                cronPromise  : null,
                showShares   : true,
                activeShare  : null,
                editingPreset: null,
                loading      : shares.length === 0
            };
        },

        created() {
            this.reloadShares(false);
            this.startPolling();
        },

        beforeDestroy() {
            this.stopPolling();
        },

        computed: {
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
                if(!this.folder.hasOwnProperty('share')) {
                    return true;
                }

                return this.folder.share === null ||
                       (
                           typeof this.folder.share !== 'string' &&
                           this.folder.share.hasOwnProperty('shareable') &&
                           this.folder.share.shareable
                       );
            },
            isSharedWithUser() {
                return this.folder.share && this.folder.share.owner;
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

                API.showFolder(this.folder.id, 'model+shares')
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
            }
        },

        watch: {
            folder(value) {
                this.shares = value.hasOwnProperty('shares') ? UtilityService.objectToArray(value.shares):[];
                this.reloadShares(this.shares.length === 0);
                this.$forceUpdate();
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
