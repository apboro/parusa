<template>
    <LayoutPage :title="$route.meta['title']">
            <LayoutRoutedTabs :tabs="{
                period: 'За период',
                shifts: 'По сменам',
            }" @change="tab = $event"/>

            <AdminPromotersRegistry v-if="tab === 'period'"/>
            <AdminPromotersRegistryShifts v-if="tab === 'shifts'"/>
    </LayoutPage>
</template>

<script>
import LayoutPage from "@/Components/Layout/LayoutPage";
import AdminPromotersRegistry from "@/Pages/Admin/Registries/Parts/AdminPromotersRegistry.vue";
import PromoterInfo from "@/Pages/Terminal/Parts/PromoterInfo.vue";
import AdminOrderRegistry from "@/Pages/Admin/Registries/Parts/AdminOrderRegistry.vue";
import LayoutRoutedTabs from "@/Components/Layout/LayoutRoutedTabs.vue";
import PromoterAccount from "@/Pages/Terminal/Parts/PromoterAccount.vue";
import PromoterTotal from "@/Pages/Terminal/Parts/PromoterTotal.vue";
import AdminPromotersRegistryShifts from "@/Pages/Admin/Registries/Parts/AdminPromotersRegistryShifts.vue";

export default {
    components: {
        AdminPromotersRegistryShifts,
        PromoterTotal, PromoterAccount, LayoutRoutedTabs, AdminOrderRegistry, PromoterInfo,
        AdminPromotersRegistry,
        LayoutPage
    },
    data: () => ({
        tab: null,
    }),
}
</script>
