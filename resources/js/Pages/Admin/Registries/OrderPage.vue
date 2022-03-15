<template>
    <AdminOrder :order-id="orderId"/>
</template>

<script>
import AdminOrder from "@/Pages/Admin/Registries/Parts/AdminOrder";

export default {
    components: {
        AdminOrder,
    },

    computed: {
        orderId() {
            return Number(this.$route.params.id);
        },
    }
}
</script>
