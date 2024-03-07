<script>
import LayoutPage from "@/Components/Layout/LayoutPage.vue";
import GuiActionsMenu from "@/Components/GUI/GuiActionsMenu.vue";
import LayoutFilters from "@/Components/Layout/LayoutFilters.vue";
import DictionaryDropDown from "@/Components/Inputs/DictionaryDropDown.vue";
import LayoutFiltersItem from "@/Components/Layout/LayoutFiltersItem.vue";
import list from "@/Core/List";
import ListTable from "@/Components/ListTable/ListTable.vue";
import Pagination from "@/Components/Pagination.vue";
import ListTableRow from "@/Components/ListTable/ListTableRow.vue";
import ListTableCell from "@/Components/ListTable/ListTableCell.vue";
import GuiMessage from "@/Components/GUI/GuiMessage.vue";
import GuiActivityIndicator from "@/Components/GUI/GuiActivityIndicator.vue";

export default {
    name: "NewsList",
    components: {
        GuiActivityIndicator,
        GuiMessage,
        ListTableCell,
        ListTableRow,
        Pagination,
        ListTable,
        LayoutFiltersItem, DictionaryDropDown, LayoutFilters, GuiActionsMenu, LayoutPage
    },
    data: () => ({
        list: list('/api/news'),
    }),
    created() {
        this.list.initial();
    },
}
</script>

<template>
    <LayoutPage :title="'Новости'">
        <ListTable :titles="['заголовок','дата']" v-if="list.list && list.list.length > 0">
            <ListTableRow v-for="news in list.list">
                <ListTableCell :class="'w-40'">
                    <router-link :class="{'new-news' : news.isNew }" class="link" :to="{ name: 'news-view', params: { id: news['id'] }}">
                        {{ news['title'] }}
                    </router-link>
                </ListTableCell>
                <ListTableCell :class="'w-30'">
                    <router-link :class="{'new-news' : news.isNew }" class="link" :to="{ name: 'news-view', params: { id: news['id'] }}">
                        {{ news['send_at'] ?? news['created_at'] }}
                    </router-link>
                </ListTableCell>
            </ListTableRow>
        </ListTable>
        <GuiMessage border v-else-if="list.is_loaded">Ничего не найдено</GuiMessage>

        <Pagination :pagination="list.pagination" @pagination="(page, per_page) => list.load(page, per_page)"/>

    </LayoutPage>
</template>

<style scoped lang="scss">
.buttons-menu {
    display: flex;
    flex-direction: column;
}
.new-news {
    font-weight: bold;
}
</style>
