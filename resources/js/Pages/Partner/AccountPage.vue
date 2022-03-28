<template>
    <LayoutPage :title="$route.meta['title']">
        <PartnerAccount/>
    </LayoutPage>
</template>

<script>
import LayoutPage from "@/Components/Layout/LayoutPage";
import PartnerAccount from "@/Pages/Partner/Parts/PartnerAccount";

export default {
    components: {
        LayoutPage,
        PartnerAccount,
    }
}
</script>
