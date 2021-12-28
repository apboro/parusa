<template>
    <list-page :loading="list.loading">

        <template v-slot:header>
            <page-title-bar :title="$route.meta['title']">
                <actions-menu>
                    <router-link :to="{ name: 'excursion-edit', params: { id: 0 }}">Добавить экскурсию</router-link>
                </actions-menu>
            </page-title-bar>
        </template>

        <template v-slot:filters>
            <page-bar-item :title="'Статус экскурсии'">
                <dictionary-drop-down
                    :dictionary="'excursion_statuses'"
                    :placeholder="'Все'"
                    :has-null="true"
                    :original="list.filters_original.status_id"
                    v-model="list.filters.status_id"
                    @changed="reload"
                />
            </page-bar-item>
        </template>

        <base-table v-if="!empty(list.data)">
            <template v-slot:header>
                <base-table-head :header="list.titles"/>
            </template>
            <base-table-row v-for="(row, key) in list.data" :key="key">
                <base-table-cell>
                    <activity :active="row.active"/>
                    <router-link class="link" :to="{ name: 'excursion-view', params: { id: row.id }}">{{ row.record['name'] }}</router-link>
                </base-table-cell>
                <base-table-cell>{{ row.record['status'] }}</base-table-cell>
            </base-table-row>
        </base-table>
        <message v-else-if="list.loaded">Ничего не найдено</message>
        <base-pagination :pagination="list.pagination" @pagination="setPagination"/>

    </list-page>
</template>

<script>
import listDataSource from "../../../../Helpers/Core/listDataSource";
import UseBaseTableBundle from "../../../../Mixins/UseBaseTableBundle";
import empty from "../../../../Mixins/empty";

import ListPage from "../../../../Layouts/ListPage";
import PageTitleBar from "../../../../Layouts/Parts/PageTitleBar";
import ActionsMenu from "../../../../Components/ActionsMenu";
import PageBarItem from "../../../../Layouts/Parts/PageBarItem";
import DictionaryDropDown from "../../../../Components/Dictionary/DictionaryDropDown";
import Activity from "../../../../Components/Activity";
import Message from "../../../../Layouts/Parts/Message";
import BasePagination from "../../../../Components/Base/BasePagination";

export default {
    components: {
        ListPage,
        PageTitleBar,
        ActionsMenu,
        PageBarItem,
        DictionaryDropDown,
        Activity,
        Message,
        BasePagination,
    },

    mixins: [UseBaseTableBundle, empty],

    data: () => ({
        list: null,
    }),

    created() {
        this.list = listDataSource('/api/excursions');
        this.list.load(1, null, true);
    },

    methods: {
        setPagination(page, perPage) {
            this.list.load(page, perPage);
        },
        reload() {
            this.list.load();
        },
    },
}
</script>
