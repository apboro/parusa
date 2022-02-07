<template>
    <page>
        <message text-red>Страница не найдена.</message>
    </page>
</template>

<script>
import Page from "@/Layouts/Page";
import Message from "@/Components/GUI/GuiMessage";

export default {
    components: {
        Message,
        Page,
    }
}
</script>
