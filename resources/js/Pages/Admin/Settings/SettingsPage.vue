<template>
    <LayoutPage :title="$route.meta['title']">
        <LayoutRoutedTabs :tabs="{general: 'Основные'}" @change="tab = $event"/>

        <GeneralSettings v-if="tab === 'general'"/>

    </LayoutPage>
</template>

<script>


import LayoutPage from "@/Components/Layout/LayoutPage";
import LayoutRoutedTabs from "@/Components/Layout/LayoutRoutedTabs";
import GeneralSettings from "@/Pages/Admin/Settings/Parts/GeneralSettings";

export default {
    components: {
        GeneralSettings,
        LayoutRoutedTabs,
        LayoutPage
    },

    data: () => ({
        tab: null,
    }),
}
</script>
