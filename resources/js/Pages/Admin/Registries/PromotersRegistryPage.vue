<template>
    <LayoutPage :title="$route.meta['title']">
            <AdminPromotersRegistryShifts/>
    </LayoutPage>
</template>

<script>
import LayoutPage from "@/Components/Layout/LayoutPage";
import AdminPromotersRegistryShifts from "@/Pages/Admin/Registries/Parts/AdminPromotersRegistryShifts.vue";

export default {
    components: {
        AdminPromotersRegistryShifts,
        LayoutPage
    },

}
</script>
