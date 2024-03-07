<template>
    <div class="application">
        <layout-header :user="user">
            <template v-slot:menu>
                <layout-menu :menu="menu" :newNews="newNews"/>
            </template>
            <template v-slot:personal>
                <layout-user-menu :user="user">
                    <span class="link" v-if="user.positions" @click="change">Сменить компанию</span>
                    <span class="link" @click="logout">Выход</span>
                </layout-user-menu>
            </template>
        </layout-header>
        <container mt-20 mb-20>
            <PartnerHeaderWidget/>
        </container>
        <router-view/>
    </div>
    <div id="toaster" class="toaster"></div>
    <div id="dialogs" class="dialogs"></div>
</template>

<script>
import LayoutHeader from "@/Components/Layout/LayoutHeader";
import LayoutMenu from "@/Components/Layout/LayoutMenu";
import LayoutUserMenu from "@/Components/Layout/LayoutUserMenu";
import PartnerHeaderWidget from "@/Apps/PartnerHeaderWidget";
import Container from "@/Components/GUI/GuiContainer";
import PopUp from "@/Components/PopUp";
import {mapState} from "vuex";

export default {
    props: {
        menu: Array,
        user: Object,
    },

    computed: {
        ...mapState('partner', {newNews: state => state.new_news})
    },

    components: {
        Container,
        PartnerHeaderWidget,
        LayoutUserMenu,
        LayoutHeader,
        LayoutMenu,
        PopUp,
    },

    created() {
        this.$store.dispatch('partner/refresh');
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
