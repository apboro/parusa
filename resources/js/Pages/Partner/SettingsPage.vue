<template>
    <LayoutPage :title="$route.meta['title']" :loading="data.is_loading">
        <LayoutRoutedTabs :tabs="{codes: 'Коды', widget: 'Виджет'}" @change="tab = $event"/>

        <PartnerCodes v-if="tab === 'codes'" :data="data.data"/>
        <WidgetCodeGenerator v-if="tab === 'widget'"/>
    </LayoutPage>
</template>

<script>
import LayoutPage from "@/Components/Layout/LayoutPage";
import LayoutRoutedTabs from "@/Components/Layout/LayoutRoutedTabs";
import data from "@/Core/Data";
import PartnerCodes from "@/Pages/Partner/Parts/PartnerCodes";
import WidgetCodeGenerator from "./Parts/WidgetCodeGenerator";

export default {
    components: {
        WidgetCodeGenerator,
        PartnerCodes,
        LayoutRoutedTabs,
        LayoutPage,
    },

    data: () => ({
        tab: 'codes',
        data: data('/api/partners/partner/settings'),
    }),

    created() {
        this.data.load();
    }
}
</script>
