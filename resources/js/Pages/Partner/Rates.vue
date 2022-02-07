<template>
    <page :title="$route.meta['title']">
        <partner-ticket-rates :partner-id="0" :editable="false"/>
    </page>
</template>

<script>
import Page from "@/Layouts/Page";
import PartnerTicketRates from "@/Parts/Partners/Partner/PartnerTicketRates";

export default {
    components: {
        Page,
        PartnerTicketRates,
    }
}
</script>
