<template>
    <LayoutPage :title="$route.meta['title']">
        <OrderRegistry/>
    </LayoutPage>
</template>

<script>
import LayoutPage from "@/Components/Layout/LayoutPage";
import OrderRegistry from "@/Pages/Parts/Registries/OrderRegistry";

export default {
    components: {
        OrderRegistry,
        LayoutPage
    },
}
</script>
