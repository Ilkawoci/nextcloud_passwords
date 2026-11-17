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
                let created = 0,
                    skipped = 0,
                    failed  = 0;

                const existing = await this.getSharedRecipientIds();

                for(let recipient of this.recipients) {
                    let userIds = recipient.isNoUser ? await this.getGroupMemberIds(recipient.id):[recipient.id];

                    for(let userId of userIds) {
                        if(existing.has(userId)) {
                            skipped++;
                            continue;
                        }

                        if(await this.createFolderShare(userId)) {
                            created++;
                            existing.add(userId);
                        } else {
                            failed++;
                        }
                    }
                }

                if(failed > 0) {
                    ToastService.error('Some users could not be added to the folder share');
                } else if(created > 0) {
                    ToastService.success('Folder shared with the selected users');
                } else if(skipped > 0) {
                    ToastService.info('The folder is already shared with the selected users');
                }
            },
            /**
             * Recipients that already have a share for this folder
             */
            async getSharedRecipientIds() {
                try {
                    let folder = await API.showFolder(this.folder.id, 'model+shares'),
                        shares = folder.shares || {};

                    return new Set(Object.values(shares).map((share) => share.receiver.id));
                } catch(e) {
                    LoggingService.error(e);

                    return new Set();
                }
            },
            /**
             * Shares are stored per user, so a group is expanded into its members,
             * the same way password group shares are handled.
             */
            async getGroupMemberIds(groupId) {
                let users = await API.resolveGroup(groupId);

                return Object.keys(users).filter((userId) => userId !== getCurrentUser().uid);
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