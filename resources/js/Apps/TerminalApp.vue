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
        <GuiContainer mt-20 mb-20>
            <TerminalHeaderWidget/>
        </GuiContainer>
        <router-view/>
    </div>
    <div id="toaster" class="toaster"></div>
    <div id="dialogs" class="dialogs"></div>
</template>

<script>
import LayoutHeader from "@/Components/Layout/LayoutHeader";
import LayoutMenu from "@/Components/Layout/LayoutMenu";
import LayoutUserMenu from "@/Components/Layout/LayoutUserMenu";
import Container from "@/Components/GUI/GuiContainer";
import GuiContainer from "@/Components/GUI/GuiContainer";
import TerminalHeaderWidget from "@/Apps/TerminalHeaderWidget";
import PopUp from "@/Components/PopUp"; // add for style import todo fix when use

export default {
    props: {
        menu: Array,
        user: Object,
    },

    components: {
        TerminalHeaderWidget,
        GuiContainer,
        Container,
        LayoutUserMenu,
        LayoutHeader,
        LayoutMenu,
        PopUp, // add for style import todo fix when use
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
