<template>
    <LayoutPage :loading="list.is_loading" :title="$route.meta['title']">
        <template #actions>
            <GuiActionsMenu>
                <router-link class="link" :to="{ name: 'terminals-edit', params: { id: 0 }}">Добавить кассу</router-link>
            </GuiActionsMenu>
        </template>

        <LayoutFilters>
            <LayoutFiltersItem :title="'Статус терминала'">
                <DictionaryDropDown
                    :dictionary="'terminal_statuses'"
                    v-model="list.filters['status_id']"
                    :original="list.filters_original['status_id']"
                    :placeholder="'Все'"
                    :has-null="true"
                    :small="true"
                    @change="list.load()"
                />
            </LayoutFiltersItem>
        </LayoutFilters>

        <ListTable v-if="list.list && list.list.length > 0" :titles="list.titles">
            <ListTableRow v-for="terminal in list.list">
                    <ListTableCell :nowrap="true">
                        <GuiActivityIndicator :active="terminal['active']"/>
                        <router-link class="link" :to="{ name: 'terminals-view', params: { id: terminal['id'] }}">Касса №{{ terminal['id'] }}</router-link>
                    </ListTableCell>
                    <ListTableCell>
                        {{ terminal['status'] }}
                    </ListTableCell>
                    <ListTableCell>
                        {{ terminal['pier'] }}
                    </ListTableCell>
                    <ListTableCell>
                        {{ terminal['place'] }}
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
import ListTable from "@/Components/ListTable/ListTable";
import ListTableRow from "@/Components/ListTable/ListTableRow";
import ListTableCell from "@/Components/ListTable/ListTableCell";
import GuiActivityIndicator from "@/Components/GUI/GuiActivityIndicator";
import GuiMessage from "@/Components/GUI/GuiMessage";
import Pagination from "@/Components/Pagination";

export default {
    components: {
        LayoutPage,
        GuiActionsMenu,
        LayoutFilters,
        LayoutFiltersItem,
        DictionaryDropDown,
        ListTable,
        ListTableRow,
        ListTableCell,
        GuiActivityIndicator,
        Pagination,
        GuiMessage,
    },

    data: () => ({
        list: list('/api/terminals'),
    }),

    created() {
        this.list.initial();
    },
}
</script>
