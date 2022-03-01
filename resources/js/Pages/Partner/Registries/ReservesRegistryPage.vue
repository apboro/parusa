<template>
    <LayoutPage :title="$route.meta['title']">
        <ReservesRegistry/>
    </LayoutPage>
</template>

<script>
import LayoutPage from "@/Components/Layout/LayoutPage";
import ReservesRegistry from "@/Pages/Parts/Registries/ReservesRegistry";

export default {
    components: {
        ReservesRegistry,
        LayoutPage
    },
}
</script>
