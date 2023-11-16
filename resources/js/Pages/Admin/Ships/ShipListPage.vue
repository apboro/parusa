<template>
    <LayoutPage :title="$route.meta['title']" :loading="list.is_loading">
        <template #actions>
            <GuiActionsMenu>
                <router-link class="link" :to="{ name: 'ship-edit', params: { id: 0 }}">Добавить теплоход</router-link>
            </GuiActionsMenu>
        </template>
        <LayoutFilters>
            <LayoutFiltersItem :title="'Владелец'">
                <DictionaryDropDown :dictionary="'providers'"
                                    :placeholder="'Все'"
                                    :has-null="true"
                                    :original="list.filters_original['provider_id']"
                                    v-model="list.filters['provider_id']"
                                    @change="list.load()"
                />
            </LayoutFiltersItem>
            <template #search>
                <LayoutFiltersItem :title="'Поиск по названию'">
                    <InputSearch v-model="list.search" @change="list.load()"/>
                </LayoutFiltersItem>
            </template>
        </LayoutFilters>

        <ListTable v-if="list.list && list.list.length > 0" :titles="list.titles">
            <ListTableRow v-for="(ship, key) in list.list" :key="key">
                <ListTableCell>
                    <GuiActivityIndicator :active="ship['active']"/>
                    <router-link class="link" :to="{ name: 'ship-view', params: { id: ship['id'] }}" v-html="highlight(ship['name'])"/>
                </ListTableCell>
                <ListTableCell>
                    {{ship.owner}}
                </ListTableCell>
                <ListTableCell>
                    {{ship.capacity}}
                </ListTableCell>
            </ListTableRow>
        </ListTable>

        <GuiMessage border v-else-if="list.is_loaded">Ничего не найдено</GuiMessage>

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
import InputSearch from "@/Components/Inputs/InputSearch";
import ListTable from "@/Components/ListTable/ListTable";
import ListTableRow from "@/Components/ListTable/ListTableRow";
import ListTableCell from "@/Components/ListTable/ListTableCell";
import GuiActivityIndicator from "@/Components/GUI/GuiActivityIndicator";
import GuiMessage from "@/Components/GUI/GuiMessage";
import Pagination from "@/Components/Pagination";
import GuiAccessIndicator from "@/Components/GUI/GuiAccessIndicator";

export default {
    components: {
        GuiAccessIndicator,
        LayoutPage,
        GuiActionsMenu,
        LayoutFilters,
        LayoutFiltersItem,
        DictionaryDropDown,
        InputSearch,
        ListTable,
        ListTableRow,
        ListTableCell,
        GuiActivityIndicator,
        GuiMessage,
        Pagination,
    },

    data: () => ({
        list: list('/api/ships'),
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
