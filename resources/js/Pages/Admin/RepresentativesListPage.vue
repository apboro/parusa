<template>
    <page>
        <template v-slot:header>{{ $route.meta.title }}</template>

        <template v-slot:footer>Здесь пагинация</template>
    </page>
</template>

<script>
import Page from "../../Layouts/Page";

export default {
    components: {
        Page
    },
}
</script>
