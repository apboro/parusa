<template>
    <page :loading="processing">
        <template v-slot:header>
            <page-title-bar :title="$route.meta['title']"/>
        </template>

        <layout-routed-tabs v-if="data.loaded" :tabs="data.data" @change="changeTab"/>

        <ships-dictionary v-if="!processing && (current === 'ships')" :dictionary="current"/>
        <generic-dictionary v-else-if="!processing" :dictionary="current"/>
    </page>
</template>

<script>
import genericDataSource from "../../../Helpers/Core/genericDataSource";
import Page from "../../../Layouts/Page";
import PageTitleBar from "../../../Layouts/Parts/PageTitleBar";
import LayoutRoutedTabs from "../../../Components/Layout/LayoutRoutedTabs";
import GenericDictionary from "../../../Parts/Dictionaries/GenericDictionary";
import ShipsDictionary from "../../../Parts/Dictionaries/ShipsDictionary";

export default {
    components: {
        ShipsDictionary,
        GenericDictionary,
        LayoutRoutedTabs,
        PageTitleBar,
        Page,
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
