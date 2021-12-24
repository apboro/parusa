<template>
    <list-page :loading="list.loading">

        <template v-slot:header>
            <page-title-bar :title="$route.meta.title">
                <actions-menu>
                    <router-link :to="{ name: 'representatives-edit', params: { id: 0 }}">Добавить представителя
                    </router-link>
                </actions-menu>
            </page-title-bar>
        </template>

        <template v-slot:search>
            <page-bar-item :title="'Поиск по ФИО, названию компании'">
                <base-icon-input v-model="list.search" @changed="reload">
                    <icon-search/>
                </base-icon-input>
            </page-bar-item>
        </template>

        <base-table v-if="!empty(list.data)">
            <template v-slot:header>
                <thead class="base-table__header">
                <tr class="base-table__header-row">
                    <td class="base-table__header-cell">{{ list.titles.name }}</td>
                    <td class="base-table__header-cell" colspan="2">
                        <span class="inline w-50">{{ list.titles.position }}</span>
                        <span class="inline w-50">{{ list.titles.contacts }}</span>
                    </td>
                </tr>
                </thead>
            </template>
            <base-table-row v-for="(row, key) in list.data" :key="key">
                <base-table-cell>
                    <base-table-cell-item :class="'mt-5 mb-5'">
                        <router-link class="link" :to="{ name: 'representatives-view', params: { id: row.id }}" v-html="$highlight(row.record['name'], list.search)"/>
                    </base-table-cell-item>
                    <base-table-cell-item v-if="!row.record['has_access']">
                        <span class="text-gray text-sm"><activity-locked :locked="true"/>доступ в систему закрыт</span>
                    </base-table-cell-item>
                </base-table-cell>
                <base-table-cell colspan="2">
                    <table class="w-100">
                        <tr v-for="partner in row.record['partners']">
                            <td class="w-50 pb-5 pt-5 va-top">
                                <base-table-cell-item>
                                    <activity-locked :locked="true" v-if="!partner.active"/>
                                    <router-link class="link" :to="{ name: 'partners-view', params: { id: partner.id }}" v-html="$highlight(partner.name, list.search)"/>
                                </base-table-cell-item>
                                <base-table-cell-item>
                                    <span class="text-gray text-sm">{{ partner.position }}</span>
                                </base-table-cell-item>
                            </td>
                            <td class="w-50 pb-5 pt-5 va-top">
                                <base-table-cell-item>
                                    <a class="link" :href="'mailto:' + partner['email']" target="_blank">{{ partner['email'] }}</a>
                                </base-table-cell-item>
                                <base-table-cell-item v-if="partner['work_phone']">
                                    <span class="text-gray text-sm">тел.: </span>{{ partner['work_phone']['number'] }}
                                    <span v-if="partner['work_phone']['additional']">доб. {{ partner['work_phone']['additional'] }}</span>
                                </base-table-cell-item>
                                <base-table-cell-item v-if="partner['mobile_phone']">
                                    <span class="text-gray text-sm">моб.: </span>{{ partner['mobile_phone'] }}
                                </base-table-cell-item>
                            </td>
                        </tr>
                        <tr v-if="row.record['partners'].length === 0">
                            <td class="w-50 pb-5 pt-5 va-top">
                            </td>
                            <td class="w-50 pb-5 pt-5 va-top">
                                <base-table-cell-item>
                                    <a class="link" :href="'mailto:' + row.record['email']" target="_blank">{{ row.record['email'] }}</a>
                                </base-table-cell-item>
                                <base-table-cell-item v-if="row.record['work_phone']">
                                    <span class="text-gray text-sm">тел.: </span>{{ row.record['work_phone'] }}
                                    <span v-if="row.record['work_phone_additional']">доб. {{ row.record['work_phone_additional'] }}</span>
                                </base-table-cell-item>
                                <base-table-cell-item v-if="row.record['mobile_phone']">
                                    <span class="text-gray text-sm">моб.: </span>{{ row.record['mobile_phone'] }}
                                </base-table-cell-item>
                            </td>
                        </tr>
                    </table>
                </base-table-cell>
            </base-table-row>
        </base-table>
        <message v-else>Ничего не найдено</message>
        <base-pagination :pagination="list.pagination" @pagination="setPagination"/>

    </list-page>
</template>

<script>
import listDataSource from "../../../../Helpers/Core/listDataSource";
import empty from "../../../../Mixins/empty";

import ListPage from "../../../../Layouts/ListPage";
import PageBarItem from "../../../../Layouts/Parts/PageBarItem";
import BasePagination from "../../../../Components/Base/BasePagination";
import DictionaryDropDown from "../../../../Components/Dictionary/DictionaryDropDown";
import BaseIconInput from "../../../../Components/Base/BaseIconInput";
import IconSearch from "../../../../Components/Icons/IconSearch";
import UseBaseTableBundle from "../../../../Mixins/UseBaseTableBundle";
import Activity from "../../../../Components/Activity";
import Message from "../../../../Layouts/Parts/Message";
import PageTitleBar from "../../../../Layouts/Parts/PageTitleBar";
import BaseButton from "../../../../Components/Base/BaseButton";
import ActionsMenu from "../../../../Components/ActionsMenu";
import ActivityLocked from "../../../../Components/ActivityLocked";

export default {
    components: {
        ActivityLocked,
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
        this.list = listDataSource('/api/representatives');
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
