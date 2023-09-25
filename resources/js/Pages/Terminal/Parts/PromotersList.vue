<template>
    <LayoutPage :loading="list.is_loading" :title="$route.meta['title']">
        <LayoutFilters>
            <template #search>
                <LayoutFiltersItem :title="'Поиск по ФИО и по ID'">
                    <InputSearch v-model="list.search" @change="list.load()"/>
                </LayoutFiltersItem>
            </template>
        </LayoutFilters>

        <ListTable v-if="list.list && list.list.length > 0" :titles="list.titles">
            <ListTableRow v-for="partner in list.list">
                <ListTableCell>
                    <GuiActivityIndicator :active="partner['active']"/>
                    <router-link class="link" :to="{ name: 'terminal-promoters-view', params: { id: partner['id'] }}" v-html="highlight(partner['name'])"/>
                </ListTableCell>
                <ListTableCell>{{partner['id']}}</ListTableCell>
                <ListTableCell>{{ partner['balance'] }} руб.</ListTableCell>
                <ListTableCell>{{ partner['limit'] }} руб.</ListTableCell>
            </ListTableRow>
        </ListTable>

        <GuiMessage v-else-if="list.is_loaded">Ничего не найдено</GuiMessage>

        <Pagination :pagination="list.pagination" @pagination="(page, per_page) => list.load(page, per_page)"/>
    </LayoutPage>
</template>

<script>
import list from "@/Core/List";
import LayoutPage from "@/Components/Layout/LayoutPage.vue";
import GuiActionsMenu from "@/Components/GUI/GuiActionsMenu.vue";
import LayoutFilters from "@/Components/Layout/LayoutFilters.vue";
import LayoutFiltersItem from "@/Components/Layout/LayoutFiltersItem.vue";
import DictionaryDropDown from "@/Components/Inputs/DictionaryDropDown.vue";
import ListTable from "@/Components/ListTable/ListTable.vue";
import InputSearch from "@/Components/Inputs/InputSearch.vue";
import ListTableRow from "@/Components/ListTable/ListTableRow.vue";
import ListTableCell from "@/Components/ListTable/ListTableCell.vue";
import GuiAccessIndicator from "@/Components/GUI/GuiAccessIndicator.vue";
import GuiActivityIndicator from "@/Components/GUI/GuiActivityIndicator.vue";
import GuiMessage from "@/Components/GUI/GuiMessage.vue";
import Pagination from "@/Components/Pagination.vue";

export default {
    components: {
        Pagination,
        GuiMessage,
        GuiActivityIndicator,
        GuiAccessIndicator,
        ListTableCell,
        ListTableRow,
        InputSearch,
        ListTable,
        DictionaryDropDown,
        LayoutFiltersItem,
        LayoutFilters,
        GuiActionsMenu,
        LayoutPage

    },

    data: () => ({
        list: list('/api/promoters'),
    }),

    created() {
        this.list.initial();
    },

    methods: {
        highlight(text) {
            return this.$highlight(text, this.list.search);
        },
    },
}
</script>
