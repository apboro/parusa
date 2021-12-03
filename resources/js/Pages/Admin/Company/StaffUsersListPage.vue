<template>
    <list-page :loading="list.loading">

        <template v-slot:header>{{ $route.meta.title }}</template>

        <template v-slot:filters>
            <page-bar-item :title="'Статус сотрудника'">
                <dictionary-drop-down :dictionary="'user_statuses'" :placeholder="'Все'" :has-null="true"/>
            </page-bar-item>
        </template>

        <template v-slot:search>
            <page-bar-item :title="'Поиск по ФИО'">
                <base-icon-input v-model="search">
                    <icon-search/>
                </base-icon-input>
            </page-bar-item>
        </template>

        <base-table v-if="list.data">
            <template v-slot:header>
                <base-table-head :header="list.titles"/>
            </template>
            <base-table-row v-for="(row, key) in list.data" :key="key">
                <base-table-cell>
                    <!-- row.active -->
                    <router-link :to="{ name: 'staff-user-view', params: { id: row.id }}">{{
                            row.record['name']
                        }}
                    </router-link>
                </base-table-cell>
                <base-table-cell>{{ row.record['position'] }}</base-table-cell>
                <base-table-cell>
                    <base-table-cell-item v-for="item in row.record['contacts']">{{ item }}</base-table-cell-item>
                </base-table-cell>
            </base-table-row>
        </base-table>
        <base-pagination :pagination="list.pagination" @pagination="setPagination"/>

    </list-page>
</template>

<script>
import listDataSource from "../../../Helpers/Core/listDataSource";

import ListPage from "../../../Layouts/ListPage";
import PageBarItem from "../../../Layouts/Parts/PageBarItem";
import BasePagination from "../../../Components/Base/BasePagination";
import DictionaryDropDown from "../../../Components/Dictionary/DictionaryDropDown";
import BaseIconInput from "../../../Components/Base/BaseIconInput";
import IconSearch from "../../../Components/Icons/IconSearch";
import UseBaseTableBundle from "../../../Mixins/UseBaseTableBundle";

export default {
    components: {
        ListPage,
        PageBarItem,
        BasePagination,
        DictionaryDropDown,
        BaseIconInput,
        IconSearch,
    },

    mixins: [UseBaseTableBundle],

    data: () => ({
        list: null,
    }),

    created() {
        this.list = listDataSource('/api/users/staff');
        this.list.load();
    },

    computed: {
        search: {
            get() {
                return this.list.search;
            },
            set(value) {
                this.list.search = value;
                this.list.load();
            }
        }
    },

    methods: {
        setPagination(page, perPage) {
            this.list.load(page, perPage);
        },
    },
}
</script>
