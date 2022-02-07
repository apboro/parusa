<template>
    <list-page :loading="list.loading">

        <template v-slot:header>
            <page-title-bar :title="$route.meta['title']">
                <actions-menu>
                    <router-link class="link" :to="{ name: 'representatives-edit', params: { id: 0 }}">Добавить представителя</router-link>
                </actions-menu>
            </page-title-bar>
        </template>

        <template v-slot:search>
            <page-bar-item :title="'Поиск по ФИО, названию компании'">
                <InputSearch v-model="list.search" @change="reload"/>
            </page-bar-item>
        </template>

        <base-table v-if="!empty(list.data)">
            <template v-slot:header>
                <thead class="base-table__header">
                <tr class="base-table__header-row">
                    <td class="base-table__header-cell">{{ list.titles['name'] }}</td>
                    <td class="base-table__header-cell" colspan="2">
                        <span class="inline w-50">{{ list.titles['position'] }}</span>
                        <span class="inline w-50">{{ list.titles['contacts'] }}</span>
                    </td>
                    <td class="base-table__header-cell">{{ list.titles['access'] }}</td>
                </tr>
                </thead>
            </template>
            <base-table-row v-for="(representative, key) in list.data" :key="key">
                <base-table-cell>
                    <base-table-cell-item :class="'mt-5 mb-5'">
                        <router-link class="link" :to="{ name: 'representatives-view', params: { id: representative['id'] }}" v-html="highlight(representative['name'])"/>
                    </base-table-cell-item>
                    <base-table-cell-item :class="'mt-5 mb-5 text-sm'" v-if="representative['is_staff'] === true">
                        Сотрудник
                    </base-table-cell-item>
                </base-table-cell>
                <base-table-cell colspan="2">
                    <table class="w-100">
                        <tr v-for="position in representative['positions']">
                            <td class="w-50 pb-5 pt-5 va-top">
                                <base-table-cell-item>
                                    <access-locked :locked="!position['active']"/>
                                    <router-link class="link" :to="{ name: 'partners-view', params: { id: position['partner_id'] }}" v-html="highlight(position['partner'])"/>
                                </base-table-cell-item>
                                <base-table-cell-item>
                                    <span class="text-gray text-sm">{{ position['title'] }}</span>
                                </base-table-cell-item>
                            </td>
                            <td class="w-50 pb-5 pt-5 va-top">
                                <base-table-cell-item>
                                    <a class="link" :href="'mailto:' + position['email']" target="_blank">{{ position['email'] }}</a>
                                </base-table-cell-item>
                                <base-table-cell-item v-if="position['work_phone']">
                                    <span class="text-gray text-sm">тел.: </span>{{ position['work_phone'] }}
                                    <span v-if="position['work_phone_additional']"> доб. {{ position['work_phone_additional'] }}</span>
                                </base-table-cell-item>
                                <base-table-cell-item v-if="position['mobile_phone']">
                                    <span class="text-gray text-sm">моб.: </span>{{ position['mobile_phone'] }}
                                </base-table-cell-item>
                            </td>
                        </tr>
                        <tr v-if="representative['positions'].length === 0">
                            <td class="w-50 pb-5 pt-5 va-top">
                            </td>
                            <td class="w-50 pb-5 pt-5 va-top">
                                <base-table-cell-item>
                                    <a class="link" :href="'mailto:' + representative['email']" target="_blank">{{ representative['email'] }}</a>
                                </base-table-cell-item>
                                <base-table-cell-item v-if="representative['work_phone']">
                                    <span class="text-gray text-sm">тел.: </span>{{ representative['work_phone'] }}
                                    <span v-if="representative['work_phone_additional']"> доб. {{ representative['work_phone_additional'] }}</span>
                                </base-table-cell-item>
                                <base-table-cell-item v-if="representative['mobile_phone']">
                                    <span class="text-gray text-sm">моб.: </span>{{ representative['mobile_phone'] }}
                                </base-table-cell-item>
                            </td>
                        </tr>
                    </table>
                </base-table-cell>
                <base-table-cell>
                    <base-table-cell-item :class="'mt-5 mb-5'">
                        <activity :active="representative['has_access']"/>
                        <span>{{ representative['has_access'] ? 'открыт' : 'закрыт' }}</span>
                    </base-table-cell-item>
                </base-table-cell>
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
import ActionsMenu from "../../../../Components/GUI/GuiActionsMenu";
import PageBarItem from "../../../../Layouts/Parts/PageBarItem";
import BaseIconInput from "../../../../Components/Base/BaseIconInput";
import IconSearch from "../../../../Components/Icons/IconSearch";
import AccessLocked from "../../../../Components/GUI/GuiAccessIndicator";
import Activity from "../../../../Components/GUI/GuiActivityIndicator";
import Message from "@/Components/GUI/GuiMessage";
import BasePagination from "../../../../Components/GUI/GuiPagination";
import InputSearch from "@/Components/Inputs/InputSearch";

export default {
    components: {
        InputSearch,
        ListPage,
        PageTitleBar,
        ActionsMenu,
        PageBarItem,
        BaseIconInput,
        IconSearch,
        AccessLocked,
        Activity,
        Message,
        BasePagination,
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
        highlight(text) {
            return this.$highlight(text, this.list.search);
        },
    },
}
</script>
