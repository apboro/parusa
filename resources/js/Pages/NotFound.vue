<template>
    <page>
        <message :error="true">Страница не найдена.</message>
    </page>
</template>

<script>
import Page from "../Layouts/Page";
import Message from "../Layouts/Parts/Message";

export default {
    components: {
        Message,
        Page,
    }
}
</script>
