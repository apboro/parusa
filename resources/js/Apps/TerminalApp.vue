<template>
    <div class="application">
        <LayoutHeader :user="user">
            <template #menu>
                <LayoutMenu :menu="menu"/>
            </template>
            <template #widgets>
                <LayoutHeaderWidget :route="{name: 'order'}" :subtitle="'Текущий заказ'" :sum="loaded ? order_amount + ' руб.' : '—'">
                    <template #icon>
                        <IconBoxOffice/>
                    </template>
                </LayoutHeaderWidget>
            </template>
            <template #personal>
                <LayoutUserMenu :user="user">
                    <span class="link" v-if="user.positions" @click="change">Сменить компанию</span>
                    <span class="link" @click="logout">Выход</span>
                </LayoutUserMenu>
            </template>
        </LayoutHeader>

        <router-view/>

    </div>
    <div id="toaster" class="toaster"></div>
    <div id="dialogs" class="dialogs"></div>
</template>

<script>
import LayoutHeader from "@/Components/Layout/LayoutHeader";
import LayoutMenu from "@/Components/Layout/LayoutMenu";
import LayoutUserMenu from "@/Components/Layout/LayoutUserMenu";
import LayoutHeaderWidget from "@/Components/Layout/LayoutHeaderWidget";
import IconBoxOffice from "@/Components/Icons/IconBoxOffice";
import PopUp from "@/Components/PopUp";
import {mapState} from "vuex";

export default {
    props: {
        menu: Array,
        user: Object,
    },

    components: {
        LayoutHeader,
        LayoutMenu,
        LayoutUserMenu,
        LayoutHeaderWidget,
        IconBoxOffice,
        PopUp,
    },

    computed: {
        ...mapState('terminal', {
            loaded: state => state.loaded,
            order_amount: state => state.order_amount,
            current: state => state.current,
        }),
    },

    data: () => ({
        updater: null,
    }),

    created() {
        this.refresh();
        this.updater = setInterval(this.refresh, 15000);
    },

    methods: {
        refresh() {
            this.$store.dispatch('terminal/refresh');
        },
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
