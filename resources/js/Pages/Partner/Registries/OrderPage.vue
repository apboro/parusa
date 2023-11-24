<template>
    <PartnerOrder :order-id="orderId"/>
</template>

<script>
import PartnerOrder from "@/Pages/Partner/Registries/Parts/PartnerOrder.vue";

export default {
    components: {
        PartnerOrder,
    },

    computed: {
        orderId() {
            return Number(this.$route.params.id);
        },
    }
}
</script>
