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
    <nc-app-sidebar
            :name="sidebar.title"
            :subtitle="subtitle"
            :subtitleTooltip="subtitleTooltip"
            :active="activeTab"
            v-on:close="close()"
            class="passwords-sidebar-folder"
            v-on:opened="opened"
            v-on:closed="closed"
    >
        <template #secondary-actions>
            <nc-action-button close-after-click @click="actions.rename()" class="passwords-folder-rename">
                <pencil-icon slot="icon" :size="20"/>
                {{ t('Rename') }}
            </nc-action-button>
            <nc-action-button close-after-click @click="actions.move()" class="passwords-folder-move">
                <folder-move-icon slot="icon" :size="20"/>
                {{ t('Move') }}
            </nc-action-button>
            <nc-action-button close-after-click @click="deleteAction" class="passwords-folder-delete">
                <trash-can-icon slot="icon" :size="20"/>
                {{ t('Delete') }}
            </nc-action-button>
        </template>
        <template #tertiary-actions>
            <img class="icon" :src="folderIcon" :title="sidebar.title" width="32" height="32" alt=""/>
            <nc-button class="folder-details-favorite" :aria-label="t(folder.favorite ? 'Remove from favorites':'Mark as favorite')" variant="tertiary" @click.prevent="actions.favorite()">
                <star-icon slot="icon" fill-color="var(--color-element-warning)" v-if="folder.favorite" :size="20"/>
                <star-outline-icon slot="icon" v-else :size="20"/>
            </nc-button>
        </template>

        <nc-app-sidebar-tab :name="t('Share')" id="share-tab" v-if="hasSharing">
            <share :folder="folder"/>
            <template #icon>
                <share-variant-icon />
            </template>
        </nc-app-sidebar-tab>
    </nc-app-sidebar>
</template>

<script>
    import API from '@js/Helper/api';
    import Application from '@js/Init/Application';
    import SettingsService from '@js/Services/SettingsService';
    import {emit} from '@nextcloud/event-bus';
    import Sidebar from "@js/Models/Sidebar/Sidebar";
    import NcAppSidebar from '@nc/NcAppSidebar.js';
    import NcAppSidebarTab from '@nc/NcAppSidebarTab.js';
    import NcButton from '@nc/NcButton.js';
    import NcActionButton from '@nc/NcActionButton.js';
    import PencilIcon from '@icon/Pencil';
    import StarIcon from '@icon/Star';
    import StarOutlineIcon from '@icon/StarOutline';
    import TrashCanIcon from '@icon/TrashCan';
    import FolderMoveIcon from '@icon/FolderMove';
    import LocalisationService from "@js/Services/LocalisationService";
    import Share from '@vc/Sidebar/PasswordSidebar/Tabs/Share';
    import LoggingService from "@js/Services/LoggingService";
    import ShareVariantIcon from "@icon/ShareVariant.vue";
    import FolderActions from "@js/Actions/Folder/FolderActions";

    export default {
        components: {
            FolderMoveIcon,
            TrashCanIcon,
            StarOutlineIcon,
            StarIcon,
            PencilIcon,
            ShareVariantIcon,
            Share,
            NcAppSidebar,
            NcActionButton,
            NcAppSidebarTab,
            NcButton
        },

        props: {
            sidebar: {
                type: Sidebar
            }
        },

        data() {
            return {
                folder : this.sidebar.item,
                actions: new FolderActions(this.sidebar.item)
            };
        },

        mounted() {
            this.loadFolder().catch(LoggingService.catch);
        },

        computed: {
            subtitle() {
                return LocalisationService.formatDate(this.folder.edited);
            },
            subtitleTooltip() {
                return LocalisationService.formatDateTime(this.folder.edited);
            },
            activeTab() {
                return `${this.sidebar.tab}-tab`;
            },
            hasSharing() {
                return SettingsService.get('server.sharing.enabled');
            },
            folderIcon() {
                return SettingsService.get('server.theme.folder.icon');
            }
        },

        methods: {
            async loadFolder() {
                this.folder = await API.showFolder(this.sidebar.item.id, 'model+shares');
                this.actions = new FolderActions(this.folder);
                emit('passwords:sidebar:updated', this.sidebar);
            },
            close() {
                Application.sidebar = null;
            },
            opened() {
                emit('passwords:sidebar:opened', this.sidebar);
            },
            closed() {
                emit('passwords:sidebar:closed', this.sidebar);
            },
            deleteAction() {
                this.actions.delete()
                    .then(() => {
                        this.close();
                    });
            }
        },

        watch: {
            sidebar: {
                handler(value) {
                    if(value) {
                        this.loadFolder().catch(LoggingService.catch);
                    }
                },
                deep: true
            }
        }
    };
</script>

<style lang="scss">
.passwords-sidebar-folder.app-sidebar {
    overflow-y : hidden;

    .app-sidebar-header__tertiary-actions {
        position : relative;

        .folder-details-favorite {
            position : absolute !important;
            right    : -15px;
            bottom   : -10px;
        }
    }

    .app-sidebar-tabs__content {
        min-height : 0;
    }
}
</style>
