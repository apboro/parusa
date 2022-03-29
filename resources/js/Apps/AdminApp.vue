<template>
    <div class="application">
        <layout-header :user="user">
            <template v-slot:menu>
                <layout-menu :menu="menu"/>
            </template>
            <template v-slot:personal>
                <layout-user-menu :user="user">
                    <span class="link" v-if="user.positions" @click="change">Сменить компанию</span>
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
import PopUp from "@/Components/PopUp";

export default {
    props: {
        menu: Array,
        user: Object,
    },

    components: {
        LayoutUserMenu,
        LayoutHeader,
        LayoutMenu,
        PopUp,
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

        change() {
            axios.post('/login/change', {})
            .then(() => {
                window.location.href = '/';
            })
        }
    },
}
</script>
