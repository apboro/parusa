<template>
    <div class="application">
        <LayoutHeader :user="user">
            <template #personal>
                <LayoutUserMenu :user="user">
                    <span class="link" @click="logout">Выход</span>
                </LayoutUserMenu>
            </template>
        </LayoutHeader>

        <ControllerPage/>

    </div>
    <div id="toaster" class="toaster"></div>
    <div id="dialogs" class="dialogs"></div>
</template>

<script>
import LayoutHeader from "@/Components/Layout/LayoutHeader";
import LayoutUserMenu from "@/Components/Layout/LayoutUserMenu";
import LayoutHeaderWidget from "@/Components/Layout/LayoutHeaderWidget";
import ControllerPage from "@/Pages/Controller/ControllerPage.vue";

export default {
    props: {
        user: Object
    },

    components: {
        ControllerPage,
        LayoutHeader,
        LayoutUserMenu,
        LayoutHeaderWidget,
    },

    data: () => ({
    }),


    methods: {
        logout() {
            axios.post('/logout', {})
                .then(() => {
                    window.location.href = '/';
                });
        },
    },
}
</script>
