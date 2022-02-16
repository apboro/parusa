<template>
    <page>
        <template v-slot:header>
            <page-title-bar :title="$route.meta['title']"/>
        </template>

        <reserves-registry/>
    </page>
</template>

<script>

import Page from "../../../Layouts/Page";
import PageTitleBar from "../../../Layouts/Parts/PageTitleBar";
import ReservesRegistry from "./Parts/ReservesRegistry";

export default {
    components: {
        ReservesRegistry,
        PageTitleBar,
        Page
    },
}
</script>
