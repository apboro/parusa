<script>
import LayoutPage from "@/Components/Layout/LayoutPage.vue";
import GuiActionsMenu from "@/Components/GUI/GuiActionsMenu.vue";

export default {
    name: "NewsList",
    components: {GuiActionsMenu, LayoutPage}
}
</script>

<template>
    <LayoutPage :title="'Новости'">
        <template #actions>
            <GuiActionsMenu>
                <router-link class="link" :to="{ name: 'news-edit', params: { id: 0 }}">Добавить новость</router-link>
            </GuiActionsMenu>
        </template>
    </LayoutPage>
</template>

<style scoped lang="scss">

</style>
