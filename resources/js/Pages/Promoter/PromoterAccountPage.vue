<template>
    <LayoutPage :title="$route.meta['title']">
        <PromoterAccount/>
    </LayoutPage>
</template>

<script>
import LayoutPage from "@/Components/Layout/LayoutPage";
import PartnerAccount from "@/Pages/Partner/Parts/PartnerAccount";
import PromoterAccount from "@/Pages/Promoter/Parts/PromoterAccount.vue";
import PromoterInfo from "@/Pages/Promoter/Parts/PromoterInfo.vue";
import data from "@/Core/Data";
import AdminOrderRegistry from "@/Pages/Admin/Registries/Parts/AdminOrderRegistry.vue";

export default {
    components: {
        AdminOrderRegistry,
        PromoterInfo,
        PromoterAccount,
        LayoutPage,
        PartnerAccount,
    },
}
</script>
