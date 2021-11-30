<template>
    <page>
        <template v-slot:header>Шапка</template>
        <p class="page__error">Домашняя страница.</p>
        <template v-slot:footer>Подвал</template>
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
