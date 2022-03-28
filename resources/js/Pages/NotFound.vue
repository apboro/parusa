<template>
    <LayoutPage>
        <GuiMessage text-red>Страница не найдена.</GuiMessage>
    </LayoutPage>
</template>

<script>
import LayoutPage from "@/Components/Layout/LayoutPage";
import GuiMessage from "@/Components/GUI/GuiMessage";

export default {
    components: {
        GuiMessage,
        LayoutPage,
    }
}
</script>
