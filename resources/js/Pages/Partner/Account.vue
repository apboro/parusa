<template>
    <page>
        <template v-slot:header>
            <page-title-bar :title="'Лицевой счёт'"/>
        </template>

        <partner-account :partner-id="0" :editable="false"/>
    </page>
</template>

<script>
import Page from "../../Layouts/Page";
import PartnerAccount from "@/Parts/Partners/Partner/PartnerAccount";
import PageTitleBar from "@/Layouts/Parts/PageTitleBar";

export default {
    components: {
        PageTitleBar,
        PartnerAccount,
        Page,
    }
}
</script>
