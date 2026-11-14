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
    <nc-list-item
            :name="revision.label"
            :title="title"
            :force-display-actions="true"
            :compact="true"
    >
        <template #icon>
            <img class="icon" :src="folderIcon" width="16" height="16" alt=""/>
        </template>
        <template #subname>
            {{ created }}
        </template>
        <template #actions>
            <nc-action-button @click="restoreAction()" v-if="!isCurrent">
                <template #icon>
                    <restore-icon/>
                </template>
                {{ t('Restore revision') }}
            </nc-action-button>
        </template>
    </nc-list-item>
</template>

<script>
    import FolderManager from '@js/Manager/FolderManager';
    import SettingsService from '@js/Services/SettingsService';
    import LoggingService from "@js/Services/LoggingService";
    import LocalisationService from "@js/Services/LocalisationService";
    import RestoreIcon from '@icon/Restore';
    import NcListItem from '@nc/NcListItem.js';
    import NcActionButton from '@nc/NcActionButton.js';

    export default {
        components: {
            RestoreIcon,
            NcListItem,
            NcActionButton
        },

        props: {
            folder: {
                type: Object
            },
            revision: {
                type: Object
            }
        },

        computed: {
            created() {
                return LocalisationService.formatDateTime(this.revision.created);
            },
            title() {
                return `${this.revision.label} – ${this.created}`;
            },
            isCurrent() {
                return this.revision.id === this.folder.revision;
            },
            folderIcon() {
                return SettingsService.get('server.theme.folder.icon');
            }
        },

        methods: {
            restoreAction() {
                FolderManager
                    .restoreRevision(this.folder, this.revision)
                    .catch(LoggingService.catch);
            }
        }
    };
</script>
