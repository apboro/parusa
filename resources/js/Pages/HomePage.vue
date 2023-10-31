<script>
import roles from "@/Mixins/roles.vue";
import TripsListPage from "@/Pages/Admin/Trips/TripsListPage.vue";
import PromotersRegistryPage from "@/Pages/Admin/Registries/PromotersRegistryPage.vue";

export default {
    name: "HomePage",
    components: {PromotersRegistryPage, TripsListPage},
    mixins: [roles],

    computed: {
        isRolePromoterManager() {
            return this.hasRole(['promoter_manager']);
        },
    }
}
</script>

<template>

    <PromotersRegistryPage :title="'Реестр по промоутерам'" v-if="isRolePromoterManager"/>
    <TripsListPage :title="'Список рейсов'" v-else/>

</template>

<style scoped lang="scss">

</style>
