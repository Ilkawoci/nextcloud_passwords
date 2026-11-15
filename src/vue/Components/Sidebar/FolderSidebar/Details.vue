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
    <div slot="details" class="details passwords-folder-info">
        <detail-field :label="t('Name')" type="text" :value="folder.label"/>
        <translate tag="div" say="Folder" v-if="parent">
            <router-link :to="parentRoute" class="link">{{ parent.label }}</router-link>
        </translate>
        <translate tag="div" say="Created on">
            <span>{{ getDateTime(folder.created) }}</span>
        </translate>
        <translate tag="div" say="Last updated">
            <span>{{ getDateTime(folder.edited) }}</span>
        </translate>
        <detail-field :label="t('Favorite')" type="text" :value="folder.favorite ? t('Yes'):t('No')"/>
        <detail-field :label="t('Hidden')" type="text" :value="folder.hidden ? t('Yes'):t('No')"/>
        <detail-field :label="t('Items (folders / passwords)')" type="text" :value="itemCount"/>
        <detail-field :label="t('Shares')" type="text" :value="shareCount"/>
        <detail-field label="Id" type="text" :value="folder.id"/>
    </div>
</template>

<script>
    import Translate from '@vc/Translate';
    import DetailField from '@vc/Sidebar/PasswordSidebar/Details/DetailField';
    import LocalisationService from "@js/Services/LocalisationService";

    export default {
        components: {
            DetailField,
            Translate
        },

        props: {
            folder: {
                type: Object
            }
        },

        computed: {
            parent() {
                if(this.folder.parent && typeof this.folder.parent !== 'string') {
                    return this.folder.parent;
                }

                return null;
            },
            parentRoute() {
                if(!this.parent || this.parent.id === '00000000-0000-0000-0000-000000000000') {
                    return {name: 'Folders'};
                }

                return {name: 'Folders', params: {folder: this.parent.id}};
            },
            itemCount() {
                let folders   = this.folder.folders && typeof this.folder.folders === 'object' ? Object.keys(this.folder.folders).length:0,
                    passwords = this.folder.passwords && typeof this.folder.passwords === 'object' ? Object.keys(this.folder.passwords).length:0;

                return `${folders} / ${passwords}`;
            },
            shareCount() {
                if(this.folder.shares && typeof this.folder.shares === 'object') {
                    return Object.keys(this.folder.shares).length;
                }

                return this.folder.shared ? 1:0;
            }
        },

        methods: {
            getDateTime(timestamp) {
                return LocalisationService.formatDateTime(timestamp);
            }
        }
    };
</script>
