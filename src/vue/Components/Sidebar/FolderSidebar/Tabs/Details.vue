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
        <translate tag="div" say="Statistics" class="header"/>
        <translate tag="div" say="Created on"><span>{{ getDateTime(folder.created) }}</span></translate>
        <translate tag="div" say="Last updated"><span>{{ getDateTime(folder.edited) }}</span></translate>
        <detail-field label="Id" type="text" :value="folder.id"/>
        <translate tag="div" say="Parent" v-if="typeof folder.parent !== 'string' && folder.parent !== null">
            <router-link :to="parentRoute" class="link">
                {{ folder.parent.label }}
            </router-link>
        </translate>
        <translate tag="div" say="Revisions">
            <translate say="{count} revisions" :variables="{count:countRevisions}"/>
        </translate>
        <translate tag="div" say="Shares">
            <translate say="{count} shares" :variables="{count:countShares}"/>
        </translate>

        <translate tag="div" say="Security" class="header"/>
        <translate tag="div" say="Encryption applied on server" title="The encryption applied by the server before storing the data in the database">
            <translate :say="getSseType"/>
        </translate>
        <translate tag="div" say="Encryption applied on client" title="The encryption applied by the client before sending the data to the server">
            <translate :say="getCseType"/>
        </translate>
    </div>
</template>

<script>
    import Translate from '@vc/Translate';
    import DetailField from '@vc/Sidebar/PasswordSidebar/Details/DetailField';
    import LocalisationService from "@js/Services/LocalisationService";

    export default {
        components: {
            Translate,
            DetailField
        },

        props: {
            folder: {
                type: Object
            }
        },

        computed: {
            countShares() {
                let count = 0;
                for(let i in this.folder.shares) {
                    if(this.folder.shares.hasOwnProperty(i)) count++;
                }
                return count;
            },
            countRevisions() {
                let count = 0;
                for(let i in this.folder.revisions) {
                    if(this.folder.revisions.hasOwnProperty(i)) count++;
                }
                return count;
            },
            parentRoute() {
                return {name: 'Folders', params: {folder: this.folder.parent.id}};
            },
            getSseType() {
                let encryption = 'Encryption'+this.folder.sseType;
                switch(this.folder.sseType) {
                    case 'none':
                        encryption = 'No encryption';
                        break;
                    case 'SSEv1r1':
                        encryption = 'Simple encryption (Gen. 1)';
                        break;
                    case 'SSEv1r2':
                        encryption = 'Simple encryption (Gen. 2)';
                        break;
                    case 'SSEv2r1':
                        encryption = 'Advanced encryption (SSE V2)';
                        break;
                }

                return LocalisationService.translate(encryption);
            },
            getCseType() {
                let encryption = 'No encryption';
                if(this.folder.cseType === 'CSEv1r1') encryption = 'Encryption with libsodium';

                return LocalisationService.translate(encryption);
            }
        },

        methods: {
            getDateTime(date) {
                return LocalisationService.formatDateTime(date);
            }
        }
    };
</script>

<style lang="scss">
.passwords-folder-info {
    padding-top    : 10px;
    display        : flex;
    flex-direction : column;
    height         : 100%;

    div:not(.header) {
        font-size     : 0.8rem;
        font-style    : italic;
        margin-bottom : 5px;
        color         : var(--color-text-maxcontrast);

        a,
        span {
            display    : block;
            font-style : normal;
            font-size  : 1rem;
            color      : var(--color-text-maxcontrast);
            text-align : right;
            cursor     : text;
            word-wrap  : break-word;
        }

        a {
            cursor : pointer;

            &:hover {
                text-decoration : underline;
            }
        }
    }

    .header {
        margin-top  : 20px;
        font-size   : 1rem;
        font-weight : bold;
        color       : var(--color-main-text);
    }
}
</style>
