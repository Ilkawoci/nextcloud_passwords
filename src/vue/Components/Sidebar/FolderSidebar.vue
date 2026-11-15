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
    <nc-app-sidebar :name="folder.label"
                    :subtitle="subtitle"
                    :active="activeTab"
                    v-on:close="close"
                    class="passwords-sidebar-folder">
        <template #secondary-actions>
            <nc-action-button close-after-click @click="actions.rename()" class="passwords-folder-rename">
                <pencil-icon slot="icon" :size="20"/>
                {{ t('Rename') }}
            </nc-action-button>
            <nc-action-button close-after-click @click="actions.move()" class="passwords-folder-move">
                <folder-move-icon slot="icon" :size="20"/>
                {{ t('Move') }}
            </nc-action-button>
            <nc-action-button close-after-click @click="actions.favorite()" class="passwords-folder-favorite">
                <star-icon slot="icon" :size="20" v-if="folder.favorite"/>
                <star-outline-icon slot="icon" v-else :size="20"/>
                {{ folder.favorite ? t('BatchActionRemoveFavorites'):t('BatchActionAddFavorites') }}
            </nc-action-button>
            <nc-action-button close-after-click @click="deleteAction" class="passwords-folder-delete">
                <trash-can-icon slot="icon" :size="20"/>
                {{ t('Delete') }}
            </nc-action-button>
        </template>
        <template #tertiary-actions>
            <folder-icon class="icon" :size="24"/>
        </template>

        <nc-app-sidebar-tab icon="icon-info" :name="t('Details')" id="details-tab">
            <folder-details :folder="folder"/>
        </nc-app-sidebar-tab>
        <nc-app-sidebar-tab :name="t('Share')" id="share-tab" v-if="hasSharing">
            <folder-sharing :folder="folder" v-on:updated="loadFolder"/>
            <template #icon>
                <share-variant-icon/>
            </template>
        </nc-app-sidebar-tab>
    </nc-app-sidebar>
</template>

<script>
    import API from '@js/Helper/api';
    import FolderActions from '@js/Actions/Folder/FolderActions';
    import Application from '@js/Init/Application';
    import SettingsService from '@js/Services/SettingsService';
    import NcAppSidebar from '@nc/NcAppSidebar.js';
    import NcAppSidebarTab from '@nc/NcAppSidebarTab.js';
    import NcActionButton from '@nc/NcActionButton.js';
    import FolderIcon from '@icon/Folder';
    import PencilIcon from '@icon/Pencil';
    import FolderMoveIcon from '@icon/FolderMove';
    import TrashCanIcon from '@icon/TrashCan';
    import StarIcon from '@icon/Star';
    import StarOutlineIcon from '@icon/StarOutline';
    import ShareVariantIcon from '@icon/ShareVariant.vue';
    import FolderDetails from '@vc/Sidebar/FolderSidebar/Details';
    import FolderSharing from '@vc/Sidebar/FolderSidebar/Sharing';
    import LoggingService from "@js/Services/LoggingService";

    export default {
        components: {
            FolderSharing,
            FolderDetails,
            ShareVariantIcon,
            StarOutlineIcon,
            StarIcon,
            TrashCanIcon,
            FolderMoveIcon,
            PencilIcon,
            FolderIcon,
            NcActionButton,
            NcAppSidebarTab,
            NcAppSidebar
        },

        props: {
            sidebar: {
                type: Object
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
                return this.folder.parent && this.folder.parent.label ? this.folder.parent.label:'';
            },
            activeTab() {
                return `${this.sidebar.tab}-tab`;
            },
            hasSharing() {
                return SettingsService.get('server.sharing.enabled') && this.folder.cseType === 'none' && !this.folder.hidden;
            }
        },

        methods: {
            async loadFolder() {
                if(this.sidebar.item.id && this.sidebar.item.id !== '00000000-0000-0000-0000-000000000000') {
                    this.folder = await API.showFolder(this.sidebar.item.id, 'model+parent+shares+folders+passwords');
                    this.actions = new FolderActions(this.folder);
                }
            },
            close() {
                Application.sidebar = null;
            },
            deleteAction() {
                this.actions.delete().then(() => {
                    this.close();
                }).catch(LoggingService.catch);
            }
        },

        watch: {
            sidebar: {
                deep: true,
                handler(sidebar) {
                    if(sidebar) this.folder = sidebar.item;
                }
            }
        }
    };
</script>
