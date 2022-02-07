<template>
    <TicketInfo :ticket-id="ticketId"/>
</template>

<script>
import TicketInfo from "@/Parts/Order/TicketInfo";

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
