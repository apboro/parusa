<template>
    <LayoutPage :title="$route.meta['title']">
        <AdminTransactionsRegistry/>
    </LayoutPage>
</template>

<script>
import LayoutPage from "@/Components/Layout/LayoutPage";
import AdminTransactionsRegistry from "@/Pages/Admin/Registries/Parts/AdminTransactionsRegistry";

export default {
    components: {
        AdminTransactionsRegistry,
        LayoutPage
    },
}
</script>
