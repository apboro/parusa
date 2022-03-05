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
            <ListTableRow v-for="(staff, key) in list.list" :key="key">
                <ListTableCell>
                    <GuiActivityIndicator :active="staff['active']"/>
                    <router-link class="link" :to="{ name: 'staff-view', params: { id: staff['id'] }}" v-html="highlight(staff['name'])"/>
                </ListTableCell>
                <ListTableCell>
                    {{ staff['position'] }}
                </ListTableCell>
                <ListTableCell>
                    <div v-if="staff['email']">
                        <a class="link" target="_blank" :href="'mailto:' + staff['email']">{{ staff['email'] }}</a>
                    </div>
                    <div v-if="staff['mobile_phone']">
                        <span>{{ staff['work_phone'] }}</span>
                        <span v-if="staff['work_phone_add']"> доб. {{ staff['work_phone_add'] }}</span>
                    </div>
                    <div v-if="staff['mobile_phone']">
                        {{ staff['mobile_phone'] }}
                    </div>
                </ListTableCell>
                <ListTableCell>
                    <GuiAccessIndicator :locked="!staff['has_access']"/>
                    <span>{{ staff['has_access'] ? 'открыт' : 'закрыт' }}</span>
                </ListTableCell>
                <ListTableCell>
                    <div v-for="role in staff['roles']">{{ role }}</div>
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
