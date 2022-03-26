<template>
    <AdminTicket :ticket-id="ticketId"/>
</template>

<script>
import AdminTicket from "@/Pages/Admin/Registries/Parts/AdminTicket";

export default {
    components: {
        AdminTicket,
    },

    computed: {
        ticketId() {
            return Number(this.$route.params.id);
        },
    }
}
</script>
