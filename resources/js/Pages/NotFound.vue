<template>
    <page>
        <message error>Страница не найдена.</message>
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
