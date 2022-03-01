<template>
    <LayoutPage :title="$route.meta['title']">
        <TicketsRegistry/>
    </LayoutPage>
</template>

<script>
import LayoutPage from "@/Components/Layout/LayoutPage";
import TicketsRegistry from "@/Pages/Parts/Registries/TicketsRegistry";

export default {
    components: {
        TicketsRegistry,
        LayoutPage
    },
}
</script>
