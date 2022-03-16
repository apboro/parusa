<template>
    <PartnerTicket :ticket-id="ticketId"/>
</template>

<script>
import PartnerTicket from "@/Pages/Partner/Registries/Parts/PartnerTicket";

export default {
    components: {
        PartnerTicket,
    },

    computed: {
        ticketId() {
            return Number(this.$route.params.id);
        },
    }
}
</script>
