<template>
    <LayoutPage :loading="list.is_loading" :title="$route.meta['title']">

        <template #actions>
            <GuiActionsMenu>
                <router-link class="link" :to="{ name: 'representatives-edit', params: { id: 0 }}">Добавить представителя</router-link>
            </GuiActionsMenu>
        </template>

        <LayoutFilters>
            <template #search>
                <LayoutFiltersItem :title="'Поиск по ФИО, названию компании'">
                    <InputSearch v-model="list.search" @change="list.load()"/>
                </LayoutFiltersItem>
            </template>
        </LayoutFilters>

        <ListTable v-if="list.hasItems()">
            <template #header>
                <thead class="list-table__header">
                <tr class="list-table__header-row">
                    <td class="list-table__header-cell">{{ list.titles['name'] }}</td>
                    <td class="list-table__header-cell" colspan="2">
                        <span class="inline w-50">{{ list.titles['position'] }}</span>
                        <span class="inline w-50">{{ list.titles['contacts'] }}</span>
                    </td>
                    <td class="list-table__header-cell">{{ list.titles['access'] }}</td>
                </tr>
                </thead>
            </template>

            <ListTableRow v-for="(representative, key) in list.list" :key="key">
                <ListTableCell>
                    <div>
                        <router-link class="link" :to="{ name: 'representatives-view', params: { id: representative['id'] }}" v-html="highlight(representative['name'])"/>
                    </div>
                    <div class="text-sm" v-if="representative['is_staff'] === true">
                        Сотрудник
                    </div>
                </ListTableCell>
                <ListTableCell colspan="2">
                    <table class="w-100">
                        <tr v-for="position in representative['positions']">
                            <td class="w-50 pb-10 va-top">
                                <div class="pb-5">
                                    <GuiAccessIndicator :locked="!position['active']"/>
                                    <router-link class="link" :to="{ name: 'partners-view', params: { id: position['partner_id'] }}" v-html="highlight(position['partner'])"/>
                                </div>
                                <div>
                                    <span class="text-gray text-sm">{{ position['title'] }}</span>
                                </div>
                            </td>
                            <td class="w-50 pb-10 va-top">
                                <div class="pb-5">
                                    <a class="link" :href="'mailto:' + position['email']" target="_blank">{{ position['email'] }}</a>
                                </div>
                                <div v-if="position['work_phone']" class="pb-5">
                                    <span class="text-gray text-sm">тел.: </span>{{ position['work_phone'] }}
                                    <span v-if="position['work_phone_additional']"> доб. {{ position['work_phone_additional'] }}</span>
                                </div>
                                <div v-if="position['mobile_phone']">
                                    <span class="text-gray text-sm">моб.: </span>{{ position['mobile_phone'] }}
                                </div>
                            </td>
                        </tr>
                        <tr v-if="representative['positions'].length === 0">
                            <td></td>
                            <td class="w-50 pb-10 va-top">
                                <div class="pb-5">
                                    <a class="link" :href="'mailto:' + representative['email']" target="_blank">{{ representative['email'] }}</a>
                                </div>
                                <div v-if="representative['work_phone']" class="pb-5">
                                    <span class="text-gray text-sm">тел.: </span>{{ representative['work_phone'] }}
                                    <span v-if="representative['work_phone_additional']"> доб. {{ representative['work_phone_additional'] }}</span>
                                </div>
                                <div v-if="representative['mobile_phone']">
                                    <span class="text-gray text-sm">моб.: </span>{{ representative['mobile_phone'] }}
                                </div>
                            </td>
                        </tr>
                    </table>
                </ListTableCell>
                <ListTableCell>
                    <div>
                        <GuiActivityIndicator :active="representative['has_access']"/>
                        <span>{{ representative['has_access'] ? 'открыт' : 'закрыт' }}</span>
                    </div>
                </ListTableCell>
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
import InputSearch from "@/Components/Inputs/InputSearch";
import ListTable from "@/Components/ListTable/ListTable";
import ListTableRow from "@/Components/ListTable/ListTableRow";
import GuiMessage from "@/Components/GUI/GuiMessage";
import Pagination from "@/Components/Pagination";
import ListTableCell from "@/Components/ListTable/ListTableCell";
import GuiActivityIndicator from "@/Components/GUI/GuiActivityIndicator";
import GuiAccessIndicator from "@/Components/GUI/GuiAccessIndicator";

export default {
    components: {
        GuiAccessIndicator,
        GuiActivityIndicator,
        ListTableCell,
        Pagination,
        GuiMessage,
        ListTableRow,
        ListTable,
        InputSearch,
        LayoutFiltersItem,
        LayoutFilters,
        GuiActionsMenu,
        LayoutPage,
    },

    data: () => ({
        list: list('/api/representatives'),
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
