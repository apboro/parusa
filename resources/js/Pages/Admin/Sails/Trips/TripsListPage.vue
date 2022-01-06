<template>
    <list-page :loading="list.loading">

        <template v-slot:header>
            <page-title-bar :title="title">
                <actions-menu>
                    <router-link :to="{ name: 'trip-edit', params: { id: 0 }, query: linkQuery}">Добавить рейс</router-link>
                </actions-menu>
            </page-title-bar>
        </template>

        <template v-slot:filters>
            <page-bar-item :class="'w-250px'" :title="'Дата'">
                <base-date-input
                    :original="list.filters_original['date']"
                    v-model="list.filters['date']"
                    @changed="dateChanged"
                />
            </page-bar-item>
            <page-bar-item :class="'w-250px'" :title="'Статус движения'">
                <dictionary-drop-down
                    :dictionary="'trip_statuses'"
                    :placeholder="'Все'"
                    :has-null="true"
                    :original="list.filters_original['status_id']"
                    v-model="list.filters['status_id']"
                    @changed="reload"
                />
            </page-bar-item>
            <page-bar-item :class="'w-250px'" :title="'Экскурсия'">
                <dictionary-drop-down
                    :dictionary="'excursions'"
                    :placeholder="'Все'"
                    :has-null="true"
                    :original="list.filters_original['excursion_id']"
                    :search="true"
                    v-model="list.filters['excursion_id']"
                    @changed="reload"
                />
            </page-bar-item>
            <page-bar-item :class="'w-250px'" :title="'Причал отправления'">
                <dictionary-drop-down
                    :dictionary="'piers'"
                    :placeholder="'Все'"
                    :has-null="true"
                    :original="list.filters_original['start_pier_id']"
                    :search="true"
                    v-model="list.filters['start_pier_id']"
                    @changed="reload"
                />
            </page-bar-item>
        </template>

        <base-table v-if="!empty(list.data)">
            <template v-slot:header>
                <base-table-head :header="list.titles"/>
            </template>
            <base-table-row v-for="(trip, key) in list.data" :key="key">
                <base-table-cell>
                    <base-table-cell-item><b>{{ trip['start_time'] }}</b></base-table-cell-item>
                    <base-table-cell-item>{{ trip['start_date'] }}</base-table-cell-item>
                </base-table-cell>
                <base-table-cell>
                    <router-link :class="'link'" :to="{name: 'trip-view', params: {id: trip['id']}}">{{ trip['id'] }}</router-link>
                </base-table-cell>
                <base-table-cell>
                    {{ trip['excursion'] }}
                </base-table-cell>
                <base-table-cell>
                    <base-table-cell-item>{{ trip['pier'] }}</base-table-cell-item>
                    <base-table-cell-item>{{ trip['ship'] }}</base-table-cell-item>
                </base-table-cell>
                <base-table-cell>0 (50)</base-table-cell>
                <base-table-cell>
                    <base-table-cell-item>
                        <span class="link" @click="statusChange(trip)">{{ trip['status'] }}</span>
                    </base-table-cell-item>
                    <base-table-cell-item>
                        <span class="link" @click="saleStatusChange(trip)">{{ trip['sale_status'] }}</span>
                    </base-table-cell-item>
                </base-table-cell>
            </base-table-row>
        </base-table>
        <message v-else-if="list.loaded">Ничего не найдено</message>

        <base-pagination :pagination="list.pagination" @pagination="setPagination"/>

        <pop-up ref="status_popup" :title="popup_title"
                :buttons="[{result: 'no', caption: 'Отмена', color: 'white'}, {result: 'yes', caption: 'OK', color: 'green'}]"
                :manual="true"
        >
            <dictionary-drop-down :dictionary="popup_dictionary" v-model="current_status" :name="'status'" :original="initial_status"/>
        </pop-up>
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
import Message from "../../../../Layouts/Parts/Message";
import BasePagination from "../../../../Components/Base/BasePagination";

import BaseInput from "../../../../Components/Base/BaseInput";
import PopUp from "../../../../Components/PopUp";
import BaseDateInput from "../../../../Components/Base/BaseDateInput";

export default {
    components: {
        BaseDateInput,
        PopUp,
        ListPage,
        PageTitleBar,
        ActionsMenu,
        PageBarItem,
        DictionaryDropDown,
        Message,
        BasePagination,

        BaseInput,
    },

    mixins: [UseBaseTableBundle, empty],

    data: () => ({
        list: null,
        popup_title: null,
        popup_dictionary: null,
        initial_status: null,
        current_status: null,
    }),

    created() {
        this.list = listDataSource('/api/trips');
        this.list.load(1, null, true);
    },

    computed: {
        linkQuery() {
            let query = {};
            if (this.list.filters['start_pier_id'] !== null) {
                query['pier'] = this.list.filters['start_pier_id'];
            }
            /**
             * For future use:
             * if (this.list.filters['excursion_id'] !== null) {
             *   query['excursion'] = this.list.filters['excursion_id'];
             * }
             */
            return query;
        },
        title() {
            return this.$route.meta['title'] + (this.list.filters['date'] ? ' на ' + this.list.filters['date'] : '');
        },
    },

    methods: {
        setPagination(page, perPage) {
            this.list.load(page, perPage);
        },
        reload() {
            this.list.load();
        },
        dateChanged(name, value) {
            if (value !== null) {
                this.list.load();
            }
        },

        statusChange(trip) {
            this.popup_title = 'Статус движения';
            this.popup_dictionary = 'trip_statuses';
            this.initial_status = Number(trip['status_id']);
            this.current_status = this.initial_status;
            this.genericStatusChange('/api/trips/status', trip['id'])
                .then(data => {
                    this.list.data.some((item, key) => {
                        if (item['id'] === trip['id']) {
                            this.list.data[key]['status'] = data['status'];
                            this.list.data[key]['status_id'] = data['status_id'];
                            return true;
                        }
                        return false;
                    })
                });
        },
        saleStatusChange(trip) {
            this.popup_title = 'Статус продаж';
            this.popup_dictionary = 'trip_sale_statuses';
            this.initial_status = Number(trip['sale_status_id']);
            this.current_status = this.initial_status;
            this.genericStatusChange('/api/trips/sale-status', trip['id'])
                .then(data => {
                    this.list.data.some((item, key) => {
                        if (item['id'] === trip['id']) {
                            this.list.data[key]['sale_status'] = data['status'];
                            this.list.data[key]['sale_status_id'] = data['status_id'];
                            return true;
                        }
                        return false;
                    })
                    // this.datasource.data['sale_status'] = data['status'];
                    // this.datasource.data['sale_status_id'] = data['status_id'];
                });
        },
        genericStatusChange(url, id) {
            return new Promise((resolve, reject) => {
                this.$refs.status_popup.show()
                    .then(result => {
                        if (result === 'yes') {
                            this.$refs.status_popup.process(true);
                            axios.post(url, {id: id, status_id: this.current_status})
                                .then(response => {
                                    this.$toast.success(response.data.message, 3000);
                                    resolve(response.data.data);
                                })
                                .catch(error => {
                                    this.$toast.error(error.response.data.message);
                                    reject();
                                })
                                .finally(() => {
                                    this.$refs.status_popup.hide();
                                })
                        } else {
                            this.$refs.status_popup.hide();
                        }
                    });
            });
        }
    },
}
</script>
