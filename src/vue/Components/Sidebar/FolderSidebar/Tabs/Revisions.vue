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
    <ul class="passwords-revision-list">
        <revision
                v-for="revision in revisions"
                :key="revision.id"
                :folder="folder"
                :revision="revision"
        />
    </ul>
</template>

<script>
    import Revision from '@vc/Sidebar/FolderSidebar/Revisions/Revision';
    import UtilityService from "@js/Services/UtilityService";

    export default {
        components: {
            Revision
        },

        props: {
            folder: {
                type: Object
            }
        },

        computed: {
            revisions() {
                return UtilityService.sortApiObjectArray(this.folder.revisions, 'created', false);
            }
        },
    };
</script>
