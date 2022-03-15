<template>
    <LayoutPage :title="$route.meta['title']">
        <AdminOrderRegistry/>
    </LayoutPage>
</template>

<script>
import LayoutPage from "@/Components/Layout/LayoutPage";
import AdminOrderRegistry from "@/Pages/Admin/Registries/Parts/AdminOrderRegistry";

export default {
    components: {
        AdminOrderRegistry,
        LayoutPage
    },
}
</script>
