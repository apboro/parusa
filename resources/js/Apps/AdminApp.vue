<template>
    <div class="application">
        <layout-header>
            <template v-slot:menu>
                <layout-menu :menu="menu"/>
            </template>
            <template v-slot:personal>
                <layout-user-menu>
                    <span class="link">Профиль</span>
                    <span class="link" @click="logout">Выход</span>
                </layout-user-menu>
            </template>
        </layout-header>
        <router-view/>
    </div>
    <div id="toaster" class="toaster"></div>
    <div id="dialogs" class="dialogs"></div>
</template>

<script>
import LayoutHeader from "../Components/Layout/LayoutHeader";
import LayoutMenu from "../Components/Layout/LayoutMenu";
import LayoutUserMenu from "../Components/Layout/LayoutUserMenu";

export default {
    props: {
        menu: Array,
    },

    components: {
        LayoutUserMenu,
        LayoutHeader,
        LayoutMenu,
    },

    created() {

    },

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
