<template>
    <page>
        <template v-slot:header>
            <page-title-bar :title="$route.meta['title']"/>
        </template>

        <order-registry/>
    </page>
</template>

<script>

import Page from "../../../Layouts/Page";
import PageTitleBar from "../../../Layouts/Parts/PageTitleBar";
import OrderRegistry from "../../Admin/Registries/Parts/OrderRegistry";

export default {
    components: {
        OrderRegistry,
        PageTitleBar,
        Page
    },
}
</script>
