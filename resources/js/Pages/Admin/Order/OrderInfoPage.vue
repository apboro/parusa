<template>
    <OrderInfo :order-id="orderId"/>
</template>

<script>
import OrderInfo from "@/Parts/Order/OrderInfo";

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
