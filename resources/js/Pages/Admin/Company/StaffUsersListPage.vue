<template>
    <list-page :loading="list.loading">

        <template v-slot:header>
            <page-title-bar :title="$route.meta.title">
                <actions-menu>
                    <span>Добавить сотрудника</span>
                </actions-menu>
            </page-title-bar>
        </template>

        <template v-slot:filters>
            <page-bar-item :title="'Статус сотрудника'">
                <dictionary-drop-down :dictionary="'user_statuses'" :placeholder="'Все'" :has-null="true"
                                      v-model="list.filters.position_status_id" @changed="reload"/>
            </page-bar-item>
        </template>

        <template v-slot:search>
            <page-bar-item :title="'Поиск по ФИО'">
                <base-icon-input v-model="list.search" @changed="reload">
                    <icon-search/>
                </base-icon-input>
            </page-bar-item>
        </template>

        <base-table v-if="!empty(list.data)">
            <template v-slot:header>
                <base-table-head :header="list.titles"/>
            </template>
            <base-table-row v-for="(row, key) in list.data" :key="key">
                <base-table-cell>
                    <!-- row.active -->
                    <activity :active="row.active"/>
                    <router-link class="link" :to="{ name: 'staff-user-view', params: { id: row.id }}">{{
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
        <message v-else>Ничего не найдено</message>
        <base-pagination :pagination="list.pagination" @pagination="setPagination"/>

    </list-page>
</template>

<script>
import listDataSource from "../../../Helpers/Core/listDataSource";
import empty from "../../../Mixins/empty";

import ListPage from "../../../Layouts/ListPage";
import PageBarItem from "../../../Layouts/Parts/PageBarItem";
import BasePagination from "../../../Components/Base/BasePagination";
import DictionaryDropDown from "../../../Components/Dictionary/DictionaryDropDown";
import BaseIconInput from "../../../Components/Base/BaseIconInput";
import IconSearch from "../../../Components/Icons/IconSearch";
import UseBaseTableBundle from "../../../Mixins/UseBaseTableBundle";
import Activity from "../../../Components/Activity";
import Message from "../../../Layouts/Parts/Message";
import PageTitleBar from "../../../Layouts/Parts/PageTitleBar";
import BaseButton from "../../../Components/Base/BaseButton";
import ActionsMenu from "../../../Components/ActionsMenu";

export default {
    components: {
        ActionsMenu,
        BaseButton,
        PageTitleBar,
        Message,
        Activity,
        ListPage,
        PageBarItem,
        BasePagination,
        DictionaryDropDown,
        BaseIconInput,
        IconSearch,
    },

    mixins: [UseBaseTableBundle, empty],

    data: () => ({
        list: null,
    }),

    created() {
        this.list = listDataSource('/api/users/staff');
        this.list.load();
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
