<template>
    <LayoutPage :title="$route.meta['title']">
        <PartnerReservesRegistry/>
    </LayoutPage>
</template>

<script>
import LayoutPage from "@/Components/Layout/LayoutPage";
import PartnerReservesRegistry from "@/Pages/Partner/Registries/Parts/PartnerReservesRegistry";

export default {
    components: {
        LayoutPage,
        PartnerReservesRegistry,
    },
}
</script>
