<template>
    <PromoterOrder :order-id="orderId"/>
</template>

<script>
import PromoterOrder from "@/Pages/Promoter/Parts/PromoterOrder.vue";

export default {
    components: {
        PromoterOrder,
    },

    computed: {
        orderId() {
            return Number(this.$route.params.id);
        },
    }
}
</script>
