<template>
    <page :title="$route.meta['title']">
        <partner-account :partner-id="0" :editable="false"/>
    </page>
</template>

<script>
import Page from "../../Layouts/Page";
import PartnerAccount from "@/Parts/Partners/Partner/PartnerAccount";

export default {
    components: {
        Page,
        PartnerAccount,
    }
}
</script>
