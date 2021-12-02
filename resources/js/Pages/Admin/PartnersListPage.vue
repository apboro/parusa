<template>
    <list-page :loading="list.loading">

        <template v-slot:header>{{ $route.meta.title }}</template>

        <template v-slot:filters>
            <dictionary-drop-down :dictionary="'partner_types'" :placeholder="'Все'" :has-null="true"/>
            <dictionary-drop-down :dictionary="'partner_statuses'" :placeholder="'Все'" :has-null="true"/>
        </template>

        <template v-slot:search>
            <dictionary-drop-down :dictionary="'partner_types'" :placeholder="'Все'" :has-null="true"/>
        </template>

        <base-table v-if="list.data">
            <template v-slot:header>
                <base-table-head :header="list.titles"/>
            </template>
            <base-table-row v-for="(row, key) in list.data" :key="key">
                <base-table-cell v-for="(cell, key) in row['record']" :key="key">{{ cell }}</base-table-cell>
            </base-table-row>
        </base-table>
        <base-pagination :pagination="list.pagination" @pagination="setPagination"/>

    </list-page>
</template>

<script>
import listDataSource from "../../Helpers/Core/listDataSource";

import ListPage from "../../Layouts/ListPage";
import BasePagination from "../../Components/Base/BasePagination";
import DictionaryDropDown from "../../Components/Dictionary/DictionaryDropDown";
import BaseTable from "../../Components/Table/BaseTable";
import BaseTableHead from "../../Components/Table/BaseTableHead";
import BaseTableRow from "../../Components/Table/BaseTableRow";
import BaseTableCell from "../../Components/Table/BaseTableCell";

export default {
    components: {
        ListPage,
        BasePagination,
        DictionaryDropDown,
        BaseTable,
        BaseTableHead,
        BaseTableRow,
        BaseTableCell,
    },

    data: () => ({
        list: null,
    }),

    created() {
        this.list = listDataSource('/api/partners');
        this.list.load();
    },

    computed: {},

    methods: {
        setPagination(page, perPage) {
            this.list.load(page, perPage);
        },
    },
}
</script>
