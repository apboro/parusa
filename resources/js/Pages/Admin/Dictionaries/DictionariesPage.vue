<template>
    <page :loading="processing">
        <template v-slot:header>
            <page-title-bar :title="$route.meta['title']"/>
        </template>

        <layout-routed-tabs v-if="data.loaded" :tabs="data.data" @change="changeTab"/>

        <dictionary v-if="!processing" :dictionary="current"/>
    </page>
</template>

<script>
import genericDataSource from "../../../Helpers/Core/genericDataSource";
import Page from "../../../Layouts/Page";
import PageTitleBar from "../../../Layouts/Parts/PageTitleBar";
import LayoutRoutedTabs from "../../../Components/Layout/LayoutRoutedTabs";
import Dictionary from "../../../Parts/Dictionaries/Dictionary";

export default {
    components: {
        Page,
        PageTitleBar,
        LayoutRoutedTabs,
        Dictionary,
    },

    data: () => ({
        initial: null,
        current: null,
        data: null,
    }),

    computed: {
        processing() {
            return (this.data.loading || this.current === null);
        },
    },

    created() {
        this.data = genericDataSource('/api/dictionaries/index');
        this.data.onLoad = this.loaded;
        this.data.load();
    },

    methods: {
        changeTab(tab) {
            this.current = tab;
        },
    }
}
</script>
