<template>
    <LayoutPage :title="$route.meta['title']">
        <PartnerTicketsRegistry/>
    </LayoutPage>
</template>

<script>
import LayoutPage from "@/Components/Layout/LayoutPage";
import PartnerTicketsRegistry from "@/Pages/Partner/Registries/Parts/PartnerTicketsRegistry";

export default {
    components: {
        PartnerTicketsRegistry,
        LayoutPage
    },
}
</script>
