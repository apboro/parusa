<template>
    <page>
        <template v-slot:header>
            <page-title-bar :title="$route.meta['title']"/>
        </template>

        <layout-routed-tabs :tabs="{
            general: 'Основные'
        }" @change="tab = $event"/>

        <general-settings v-if="tab === 'general'"/>

    </page>
</template>

<script>

import Page from "../../../Layouts/Page";
import PageTitleBar from "../../../Layouts/Parts/PageTitleBar";
import LayoutRoutedTabs from "../../../Components/Layout/LayoutRoutedTabs";
import GeneralSettings from "../../../Parts/Settings/GeneralSettings";

export default {
    components: {
        GeneralSettings,
        LayoutRoutedTabs,
        Page,
        PageTitleBar,
    },

    data: () => ({
        tab: null,
    }),
}
</script>
