<template>
    <LayoutPage :title="$route.meta['title']" :loading="data.is_loading">
        <LayoutRoutedTabs :tabs="{codes: 'Коды', widget2: 'Виджет 2'}"
                          @change="tab = $event"
        />

        <PartnerCodes v-if="tab === 'codes'" :data="data.data"/>
        <PartnerCodes2 v-if="tab === 'widget2'" :data="widget2.data"/>
    </LayoutPage>
</template>

<script>
import LayoutPage from "@/Components/Layout/LayoutPage";
import LayoutRoutedTabs from "@/Components/Layout/LayoutRoutedTabs";
import PartnerCodes from "@/Pages/Partner/Parts/PartnerCodes";
import data from "@/Core/Data";
import PartnerCodes2 from "@/Pages/Partner/Parts/PartnerCodes2.vue";

export default {
    components: {
        PartnerCodes2,
        PartnerCodes,
        LayoutRoutedTabs,
        LayoutPage,
    },

    data: () => ({
        tab: null,
        data: data('/api/partners/partner/settings'),
        widget2: data('/api/partners/partner/settings/widget2'),
    }),

    created() {
        this.data.load();
        this.widget2.load();
    }
}
</script>
