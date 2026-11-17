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
    <div class="share-edit-form">
        <share-user-select v-model="recipients" v-if="share === null"/>
        <nc-note-card type="info" v-else>
            <div class="share-receiver">
                {{ t('ShareEditUser', {name: share.receiver.name}) }}
            </div>
            <template #icon>
                <nc-avatar disable-menu :size="32" :user="share.receiver.id" :display-name="share.receiver.name"/>
            </template>
        </nc-note-card>
        <nc-note-card type="info" :text="t('ShareGroupShareWarning')" v-if="groupShareWarning"/>

        <share-options-form
                :share="share"
                :can-edit="canEdit"
                :with-overwrite="share === null"
                :default-preset="defaultPreset"
                :default-expires="defaultExpires"
                v-on:options="setOptions"
                v-if="isReadyForConfigure"
        />

        <div class="share-edit-actions" v-if="isReadyForConfigure">
            <nc-button @click="cancelAction" type="secondary">
                {{ t('Cancel') }}
            </nc-button>
            <nc-button @click="revokeAction" type="error" v-if="share !== null">
                {{ t('ShareRevokeAccess') }}
            </nc-button>
            <nc-button @click="saveAction" type="primary">
                {{ t(share === null ? 'ShareActionCreate':'ShareActionUpdate') }}
            </nc-button>
        </div>
    </div>
</template>

<script>

    import ShareUserSelect from "@vc/Sidebar/PasswordSidebar/Sharing/ShareUserSelect.vue";
    import ShareOptionsForm from "@vc/Sharing/ShareOptionsForm.vue";
    import NcButton from "@nextcloud/vue/components/NcButton";
    import ShareBatchAction from "@js/Actions/BatchActions/ShareBatchAction";
    import LoggingService from "@js/Services/LoggingService";
    import UtilityService from "@js/Services/UtilityService";
    import API from "@js/Helper/api";
    import ToastService from "@js/Services/ToastService";
    import NcAvatar from "@nextcloud/vue/components/NcAvatar";
    import NcNoteCard from "@nextcloud/vue/components/NcNoteCard";
    import {getCurrentUser} from '@nextcloud/auth';

    export default {
        components: {
            ShareOptionsForm,
            ShareUserSelect,
            NcButton,
            NcAvatar,
            NcNoteCard
        },
        props     : {
            password     : Object,
            folder       : {
                type   : Object,
                default: null
            },
            share        : {
                type   : Object,
                default: () => {
                    return null;
                }
            },
            defaultPreset: {
                type   : String,
                default: null
            }
        },
        data() {
            return {
                recipients: [],
                options   : null
            };
        },
        computed: {
            isReadyForConfigure() {
                return this.recipients.length > 0 || this.share !== null;
            },
            target() {
                return this.folder || this.password;
            },
            canEdit() {
                let share = this.target.share;

                if(share === null || share === undefined) {
                    return true;
                }

                return share.editable === true;
            },
            defaultExpires() {
                let share = this.target.share;

                if(share && typeof share !== 'string') {
                    return share.expires;
                }

                return null;
            },
            groupShareWarning() {
                return this.recipients && this.recipients.some(item => item.isNoUser);
            }
        },
        methods : {
            setOptions(d) {
                this.options = d;

                this.$emit('configuring', true);
            },
            cancelAction() {
                this.$emit('cancel');
                this.$emit('configuring', false);
                this.resetForm();
            },
            async revokeAction() {
                await API.deleteShare(this.share.id);
                ToastService.success('ShareDeletedToast');

                this.$emit('revoke', this.share);
                this.$emit('configuring', false);
                this.resetForm();
            },
            async saveAction() {
                if(!this.share) {
                    await this.createAction();
                } else {
                    await this.updateAction();
                }

                this.$emit('save', this.share);
                this.$emit('configuring', false);
                this.resetForm();
            },
            async createAction() {
                if(this.folder) {
                    await this.createFolderShares();

                    return;
                }

                this.options.recipients = this.recipients;

                const action = new ShareBatchAction(
                    {
                        folders  : [],
                        passwords: [this.password],
                        tags     : []
                    },
                    this.options
                );

                await action.run().catch(LoggingService.catch);
            },
            async createFolderShares() {
                let created = 0;

                for(let recipient of this.recipients) {
                    if(recipient.isNoUser) {
                        created += await this.createFolderGroupShares(recipient.id);
                    } else {
                        created += await this.createFolderShare(recipient.id) ? 1:0;
                    }
                }

                if(created > 0) {
                    ToastService.success('Folder shared with the selected users');
                } else {
                    ToastService.error('Could not share the folder with the selected users');
                }
            },
            /**
             * Shares are stored per user, so a group is expanded into its members,
             * the same way password group shares are handled.
             */
            async createFolderGroupShares(groupId) {
                let created = 0,
                    users   = await API.resolveGroup(groupId);

                for(let userId in users) {
                    if(!users.hasOwnProperty(userId) || userId === getCurrentUser().uid) continue;

                    if(await this.createFolderShare(userId)) created++;
                }

                return created;
            },
            async createFolderShare(recipientId) {
                let expires = this.options.expires instanceof Date
                    ? Math.floor(this.options.expires.getTime() / 1000):this.options.expires;

                try {
                    await API.createShare(
                        {
                            folder   : this.folder.id,
                            recipient: recipientId,
                            type     : 'user',
                            expires  : expires ?? null,
                            editable : !!this.options.editable,
                            shareable: !!this.options.shareable
                        }
                    );

                    return true;
                } catch(e) {
                    LoggingService.error(e);

                    return false;
                }
            },
            async updateAction() {
                let share = UtilityService.cloneObject(this.share);
                share.editable = this.options.editable;
                share.shareable = this.options.shareable;
                share.expires = this.options.expires;

                API.updateShare(share);
            },
            resetForm() {
                this.recipients = [];
                this.options = null;
            }
        }
    };
</script>


<style scoped lang="scss">
.share-edit-form {
    .share-receiver {
        height      : 100%;
        display     : flex;
        align-items : center;
    }

    .share-options-container {
        margin-top : 1rem;
    }

    .share-edit-actions {
        display         : flex;
        justify-content : space-between;
        width           : 100%;
        margin-top      : 2rem;
    }
}
</style>