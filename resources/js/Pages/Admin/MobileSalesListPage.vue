<template>
    <page>
        <template v-slot:header>Мобильные кассы</template>
        <!-- list here -->
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
