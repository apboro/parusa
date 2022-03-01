<template>
    <TicketInfo :ticket-id="ticketId"/>
</template>

<script>
import TicketInfo from "@/Pages/Parts/Registries/TicketInfo";

export default {
    components: {
        TicketInfo,
    },

    computed: {
        ticketId() {
            return Number(this.$route.params.id);
        },
    }
}
</script>
