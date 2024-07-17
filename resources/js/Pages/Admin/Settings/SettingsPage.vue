<template>
    <LayoutPage :title="$route.meta['title']">
        <LayoutRoutedTabs :tabs="{general: 'Основные', yaga: 'Афиша'}" @change="tab = $event"/>

        <GeneralSettings v-if="tab === 'general'"/>
        <YagaSettings v-if="tab === 'yaga'"/>

    </LayoutPage>
</template>

<script>


import LayoutPage from "@/Components/Layout/LayoutPage";
import LayoutRoutedTabs from "@/Components/Layout/LayoutRoutedTabs";
import GeneralSettings from "@/Pages/Admin/Settings/Parts/GeneralSettings";
import YagaSettings from "@/Pages/Admin/Settings/Parts/YagaSettings.vue";

export default {
    components: {
        YagaSettings,
        GeneralSettings,
        LayoutRoutedTabs,
        LayoutPage
    },

    data: () => ({
        tab: null,
    }),
}
</script>
