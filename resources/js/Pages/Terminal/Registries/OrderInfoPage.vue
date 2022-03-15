<template>
    <OrderInfo :order-id="orderId"/>
</template>

<script>
import OrderInfo from "@/Pages/Parts/Registries/OrderInfo";

export default {
    components: {
        OrderInfo,
    },

    computed: {
        orderId() {
            return Number(this.$route.params.id);
        },
    }
}
</script>
