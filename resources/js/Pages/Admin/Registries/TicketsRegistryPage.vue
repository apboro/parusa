<template>
    <page>
        <template v-slot:header>
            <page-title-bar :title="$route.meta['title']"/>
        </template>

        <tickets-registry/>
    </page>
</template>

<script>

import Page from "../../../Layouts/Page";
import PageTitleBar from "../../../Layouts/Parts/PageTitleBar";
import TicketsRegistry from "../../../Parts/Registries/TicketsRegistry";

export default {
    components: {
        TicketsRegistry,
        PageTitleBar,
        Page
    },
}
</script>
