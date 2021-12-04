<template>
    <div class="application">
        <layout-header>
            <template v-slot:menu>
                <layout-menu :menu="menu"/>
            </template>
            <template v-slot:personal>
                <div>User</div>
            </template>
        </layout-header>
        <router-view/>
    </div>
</template>

<script>
import LayoutHeader from "../Components/Layout/LayoutHeader";
import LayoutMenu from "../Components/Layout/LayoutMenu";

export default {
    props: {
        menu: Array,
    },

    components: {
        LayoutHeader,
        LayoutMenu,
    },

    created() {
        this.$router.afterEach((to, from) => {
            this.$toast.clear();
        })
    },
}
</script>
