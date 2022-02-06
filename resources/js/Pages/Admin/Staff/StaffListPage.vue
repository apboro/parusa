<template>
    <LayoutPage :title="$route.meta['title']" :loading="list.is_loading">
        <template #actions>
            <GuiActionsMenu>
                <router-link class="link" :to="{ name: 'staff-edit', params: { id: 0 }}">Добавить сотрудника</router-link>
            </GuiActionsMenu>
        </template>
        <LayoutFilters>
            <LayoutFiltersItem :title="'Статус сотрудника'">
                <DictionaryDropDown
                    :dictionary="'position_statuses'"
                    v-model="list.filters['position_status_id']"
                    :original="list.filters_original['position_status_id']"
                    :placeholder="'Все'"
                    :has-null="true"
                    :small="true"
                    @change="list.load()"
                />
            </LayoutFiltersItem>
            <template #search>
                <LayoutFiltersItem :title="'Поиск по ФИО'">
                    <InputSearch v-model="list.search" @change="list.load()"/>
                </LayoutFiltersItem>
            </template>
        </LayoutFilters>

        <ListTable v-if="list.list && list.list.length > 0" :titles="list.titles">
            <ListTableRow v-for="(row, key) in list.list" :key="key">
                <ListTableCell>
                    <GuiActivityIndicator :active="row.active"/>
                    <router-link class="link" :to="{ name: 'staff-view', params: { id: row.id }}" v-html="highlight(row.record['name'])"/>
                </ListTableCell>
                <ListTableCell>
                    {{ row.record['position'] }}
                </ListTableCell>
                <ListTableCell>
                    <div v-if="row.record['contacts']['email']">
                        <a class="link" target="_blank" :href="'mailto:' + row.record['contacts']['email']">{{ row.record['contacts']['email'] }}</a>
                    </div>
                    <div v-if="row.record['contacts']['mobile_phone']">
                        <span>{{ row.record['contacts']['work_phone'] }}</span>
                        <span v-if="row.record['contacts']['work_phone_add']"> доб. {{ row.record['contacts']['work_phone_add'] }}</span>
                    </div>
                    <div v-if="row.record['contacts']['mobile_phone']">
                        {{ row.record['contacts']['mobile_phone'] }}
                    </div>
                </ListTableCell>
                <ListTableCell>
                    <GuiAccessIndicator :locked="!row.record['has_access']"/>
                    <span>{{ row.record['has_access'] ? 'открыт' : 'закрыт' }}</span>
                </ListTableCell>
            </ListTableRow>
        </ListTable>

        <GuiMessage border v-else-if="list.is_loaded">Ничего не найдено</GuiMessage>

        <GuiPagination :pagination="list.pagination" @pagination="(page, per_page) => list.load(page, per_page)"/>
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
import GuiPagination from "@/Components/GUI/GuiPagination";
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
        GuiPagination,
    },

    data: () => ({
        list: list('/api/staff'),
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
