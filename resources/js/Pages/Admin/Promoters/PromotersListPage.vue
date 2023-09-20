<template>
    <LayoutPage :loading="list.is_loading" :title="$route.meta['title']">
        <template #actions>
            <GuiActionsMenu>
                <router-link class="link" :to="{ name: 'promoters-edit', params: { id: 0 }}">Добавить промоутера</router-link>
            </GuiActionsMenu>
        </template>

        <LayoutFilters>
            <LayoutFiltersItem :title="'Статус промоутера'">
                <DictionaryDropDown
                    :dictionary="'partner_statuses'"
                    v-model="list.filters['partner_status_id']"
                    :original="list.filters_original['partner_status_id']"
                    :placeholder="'Все'"
                    :has-null="true"
                    @change="list.load()"
                />
            </LayoutFiltersItem>
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
                    <router-link class="link" :to="{ name: 'promoters-view', params: { id: partner['id'] }}" v-html="highlight(partner['name'])"/>
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
import LayoutPage from "@/Components/Layout/LayoutPage";
import GuiActionsMenu from "@/Components/GUI/GuiActionsMenu";
import LayoutFilters from "@/Components/Layout/LayoutFilters";
import LayoutFiltersItem from "@/Components/Layout/LayoutFiltersItem";
import DictionaryDropDown from "@/Components/Inputs/DictionaryDropDown";
import ListTable from "@/Components/ListTable/ListTable";
import InputSearch from "@/Components/Inputs/InputSearch";
import ListTableRow from "@/Components/ListTable/ListTableRow";
import ListTableCell from "@/Components/ListTable/ListTableCell";
import GuiAccessIndicator from "@/Components/GUI/GuiAccessIndicator";
import GuiActivityIndicator from "@/Components/GUI/GuiActivityIndicator";
import GuiMessage from "@/Components/GUI/GuiMessage";
import Pagination from "@/Components/Pagination";

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
