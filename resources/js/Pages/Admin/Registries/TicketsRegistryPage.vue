<template>
    <LayoutPage :title="$route.meta['title']">
        <AdminTicketsRegistry/>
    </LayoutPage>
</template>

<script>
import LayoutPage from "@/Components/Layout/LayoutPage";
import AdminTicketsRegistry from "@/Pages/Admin/Registries/Parts/AdminTicketsRegistry";

export default {
    components: {
        AdminTicketsRegistry,
        LayoutPage
    },
}
</script>
