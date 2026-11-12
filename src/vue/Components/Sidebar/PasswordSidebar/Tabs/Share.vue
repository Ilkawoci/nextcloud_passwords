<template>
    <sharing :password="password" :folder="folder" class="password-share-nextcloud"/>
</template>
<script>
    import Sharing from "@vc/Sidebar/PasswordSidebar/Sharing/Sharing";

    export default {
        components: {
            Sharing,
        },

        props: {
            password: {
                type   : Object,
                default: null
            },
            folder: {
                type   : Object,
                default: null
            }
        }
    }
</script>