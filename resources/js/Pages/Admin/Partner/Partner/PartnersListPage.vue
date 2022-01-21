<template>
    <list-page :loading="list.loading">

        <template v-slot:header>
            <page-title-bar :title="$route.meta['title']">
                <actions-menu>
                    <router-link :to="{ name: 'partners-edit', params: { id: 0 }}">Добавить партнёра
                    </router-link>
                </actions-menu>
            </page-title-bar>
        </template>

        <template v-slot:filters>
            <page-bar-item :title="'Статус партнёра'">
                <dictionary-drop-down
                    :dictionary="'partner_statuses'"
                    :placeholder="'Все'"
                    :has-null="true"
                    :original="list.filters_original['partner_status_id']"
                    v-model="list.filters['partner_status_id']"
                    @changed="reload"
                />
            </page-bar-item>
            <page-bar-item :title="'Тип партнёра'">
                <dictionary-drop-down
                    :dictionary="'partner_types'"
                    :placeholder="'Все'"
                    :has-null="true"
                    v-model="list.filters['partner_type_id']"
                    @changed="reload"
                />
            </page-bar-item>
        </template>

        <template v-slot:search>
            <page-bar-item :title="'Поиск по названию, ФИО представителя'">
                <base-icon-input v-model="list.search" @changed="reload">
                    <icon-search/>
                </base-icon-input>
            </page-bar-item>
        </template>

        <base-table v-if="!empty(list.data)">
            <template v-slot:header>
                <base-table-head :header="list.titles"/>
            </template>
            <base-table-row v-for="(partner, key) in list.data" :key="key">
                <base-table-cell>
                    <activity :active="partner['active']"/>
                    <router-link class="link" :to="{ name: 'partners-view', params: { id: partner['id'] }}" v-html="highlight(partner['name'])"/>
                </base-table-cell>
                <base-table-cell>
                    <base-table-cell-item v-for="representative in partner['representatives']">
                        <access-locked :locked="!representative['active']"/>
                        <router-link class="link" :to="{name: 'representatives-view', params: {id: representative['id']}}" v-html="highlight(representative['name'])"/>
                    </base-table-cell-item>
                </base-table-cell>
                <base-table-cell>{{ partner['type'] }}</base-table-cell>
                <base-table-cell>{{ partner['balance'] }} руб.</base-table-cell>
                <base-table-cell>{{ partner['limit'] }} руб.</base-table-cell>
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
import BaseIconInput from "../../../../Components/Base/BaseIconInput";
import IconSearch from "../../../../Components/Icons/IconSearch";
import Activity from "../../../../Components/Activity";
import AccessLocked from "../../../../Components/AccessLocked";
import Message from "@/Components/GUI/Message";
import BasePagination from "../../../../Components/Base/BasePagination";

export default {
    components: {
        ListPage,
        PageTitleBar,
        ActionsMenu,
        PageBarItem,
        DictionaryDropDown,
        BaseIconInput,
        IconSearch,
        Activity,
        AccessLocked,
        Message,
        BasePagination,
    },

    mixins: [UseBaseTableBundle, empty],

    data: () => ({
        list: null,
    }),

    created() {
        this.list = listDataSource('/api/partners');
        this.list.load(1, null, true);
    },

    methods: {
        setPagination(page, perPage) {
            this.list.load(page, perPage);
        },
        reload() {
            this.list.load();
        },
        highlight(text) {
            return this.$highlight(text, this.list.search);
        },
    },
}
</script>
