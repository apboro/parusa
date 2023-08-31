<template>

    <LayoutPage :title="$route.meta['title']" :loading="list.is_loading">
        <template v-if="accepted" v-slot:actions>
            <GuiActionsMenu>
                <router-link class="link" :to="{ name: 'pier-edit', params: { id: 0 }}">Добавить причал</router-link>
            </GuiActionsMenu>
        </template>

        <LayoutFilters>
            <LayoutFiltersItem :title="'Статус причала'">
                <DictionaryDropDown :dictionary="'pier_statuses'"
                                    :placeholder="'Все'"
                                    :has-null="true"
                                    :original="list.filters_original['status_id']"
                                    v-model="list.filters['status_id']"
                                    @change="list.load()"
                />
            </LayoutFiltersItem>
          <LayoutFiltersItem :title="'Владелец'">
                <DictionaryDropDown :dictionary="'providers'"
                                    :placeholder="'Все'"
                                    :has-null="true"
                                    :original="list.filters_original['provider_id']"
                                    v-model="list.filters['provider_id']"
                                    @change="list.load()"
                />
            </LayoutFiltersItem>
        </LayoutFilters>

        <ListTable v-if="list.list && list.list.length > 0" :titles="list.titles">
            <ListTableRow v-for="pier in list.list">
                <ListTableCell :class="'w-60'">
                    <GuiActivityIndicator :active="pier['active']"/>
                    <router-link class="link" :to="{ name: 'pier-view', params: { id: pier['id'] }}">{{ pier['name'] }}</router-link>
                </ListTableCell>
                <ListTableCell :class="'w-20'">
                    {{ pier['status'] }}
                </ListTableCell>
                <ListTableCell :class="'w-20'">
                    {{ pier['provider_name'] }}
                </ListTableCell>
            </ListTableRow>
        </ListTable>


        <GuiMessage v-else-if="list.is_loaded" border>Ничего не найдено</GuiMessage>

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
import roles from "@/Mixins/roles.vue";

export default {
    components: {
        LayoutPage,
        GuiActionsMenu,
        LayoutFilters, LayoutFiltersItem,
        DictionaryDropDown,
        ListTable, ListTableRow, ListTableCell,
        GuiActivityIndicator,
        GuiMessage,
        Pagination,
    },
    mixins:[ roles ],

    data: () => ({
        list: list('/api/piers'),
    }),

    created() {
        this.list.initial();
    },
    computed: {
        accepted() {
            return this.hasRole(['admin', 'office_manager']);
        },
    },
}
</script>
