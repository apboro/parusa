<template>
    <LayoutPage :title="$route.meta['title']" :loading="data.is_loading">
        <LayoutRoutedTabs :tabs="{codes: 'Коды'}"
                          @change="tab = $event"
        />

        <PartnerCodes v-if="tab === 'codes'" :data="data.data"/>
    </LayoutPage>
</template>

<script>
import LayoutPage from "@/Components/Layout/LayoutPage";
import LayoutRoutedTabs from "@/Components/Layout/LayoutRoutedTabs";
import PartnerCodes from "@/Pages/Partner/Parts/PartnerCodes";
import data from "@/Core/Data";

export default {
    components: {
        PartnerCodes,
        LayoutRoutedTabs,
        LayoutPage,
    },

    data: () => ({
        tab: null,
        data: data('/api/partners/partner/settings'),
    }),

    created() {
        this.data.load();
    }
}
</script>
