<template>
    <LayoutPage :title="$route.meta['title']">
        <PartnerTicketRates/>
    </LayoutPage>
</template>

<script>
import PartnerTicketRates from "@/Pages/Partner/Parts/PartnerTicketRates";
import LayoutPage from "@/Components/Layout/LayoutPage";

export default {
    components: {
        LayoutPage,
        PartnerTicketRates,
    }
}
</script>
