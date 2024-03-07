<script>
import GuiActionsMenu from "@/Components/GUI/GuiActionsMenu.vue";
import LayoutPage from "@/Components/Layout/LayoutPage.vue";
import TripsList from "@/Pages/Admin/Trips/Parts/TripsList.vue";
import GuiHeading from "@/Components/GUI/GuiHeading.vue";
import LayoutRoutedTabs from "@/Components/Layout/LayoutRoutedTabs.vue";
import DeleteEntry from "@/Mixins/DeleteEntry.vue";
import data from "@/Core/Data";
import GuiValue from "@/Components/GUI/GuiValue.vue";
import GuiFilesList from "@/Components/GUI/GuiFilesList.vue";
import GuiContainer from "@/Components/GUI/GuiContainer.vue";
import GuiButton from "@/Components/GUI/GuiButton.vue";
import InputText from "@/Components/Inputs/InputText.vue";
import InputString from "@/Components/Inputs/InputString.vue";

export default {
    name: "NewsViewPage",
    components: {
        InputString,
        InputText,
        GuiButton,
        GuiContainer,
        GuiFilesList, GuiValue, LayoutRoutedTabs, GuiHeading, TripsList, LayoutPage, GuiActionsMenu
    },
    mixins: [DeleteEntry],

    data: () => ({
        data: data('/api/news/view'),
        testEmail: null,
    }),

    computed: {
        newsId() {
            return Number(this.$route.params.id);
        },

        processing() {
            return this.deleting || this.data.loading;
        },
    },

    created() {
        this.data.load({id: this.newsId});
    },
    mounted() {
        this.$store.dispatch('partner/refresh')
    }
}
</script>

<template>
    <LayoutPage :loading="processing" :title="data.data['title']"
                :breadcrumbs="[{caption: 'Новости', to: {name: 'news-list'}}]"
                :link="{name: 'news-list'}"
                :link-title="'К списку новостей'"
    >
        <GuiValue :title="'Заголовок'">{{ data.data['title'] }}</GuiValue>
        <GuiValue :title="'Дата создания'">{{ data.data['created_at'] }}</GuiValue>

        <div id="description-container" v-html="data.data['description']"></div>

    </LayoutPage>

</template>

<style lang="scss">
#description-container img {
    max-width: 100%;
    height: auto;
}
</style>
