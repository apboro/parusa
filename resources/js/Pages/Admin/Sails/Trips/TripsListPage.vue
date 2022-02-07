<template>
    <list-page :loading="list.loading">

        <template v-slot:header>
            <page-title-bar :title="title">
                <actions-menu>
                    <router-link class="link" :to="{ name: 'trip-edit', params: { id: 0 }, query: linkQuery}">Добавить рейс</router-link>
                </actions-menu>
            </page-title-bar>
        </template>

        <template v-slot:filters>
            <page-bar-item :class="'w-25'" :title="'Дата'">
                <ButtonIcon :class="'mr-5'" :border="false" @click="setDay(-1)">
                    <IconBackward/>
                </ButtonIcon>
                <base-date-input
                    :original="list.filters_original['date']"
                    v-model="list.filters['date']"
                    @changed="dateChanged"
                />
                <ButtonIcon :class="'ml-5'" :border="false" @click="setDay(1)">
                    <IconForward/>
                </ButtonIcon>
            </page-bar-item>
            <page-bar-item :class="'w-25'" :title="'Статус движения'">
                <DictionaryDropDown
                    :dictionary="'trip_statuses'"
                    v-model="list.filters['status_id']"
                    :original="list.filters_original['status_id']"
                    :placeholder="'Все'"
                    :has-null="true"
                    :small="true"
                    @change="reload"
                />
            </page-bar-item>
            <page-bar-item :class="'w-25'" :title="'Экскурсия'">
                <DictionaryDropDown
                    :dictionary="'excursions'"
                    v-model="list.filters['excursion_id']"
                    :original="list.filters_original['excursion_id']"
                    :placeholder="'Все'"
                    :has-null="true"
                    :search="true"
                    :small="true"
                    @change="reload"
                />
            </page-bar-item>
            <page-bar-item :class="'w-25'" :title="'Причал отправления'">
                <DictionaryDropDown
                    :dictionary="'piers'"
                    v-model="list.filters['start_pier_id']"
                    :original="list.filters_original['start_pier_id']"
                    :placeholder="'Все'"
                    :has-null="true"
                    :search="true"
                    :right="true"
                    :small="true"
                    @change="reload"
                />
            </page-bar-item>
        </template>

        <base-table v-if="!empty(list.data)">
            <template v-slot:header>
                <base-table-head :header="list.titles" :has-actions="true"/>
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
                <base-table-cell>{{ trip['tickets_total'] - trip['tickets_count'] }} ({{ trip['tickets_total'] }})</base-table-cell>
                <base-table-cell>
                    <base-table-cell-item>
                        <span class="link" @click="statusChange(trip)">{{ trip['status'] }}</span>
                    </base-table-cell-item>
                    <base-table-cell-item>
                        <span class="link" v-if="trip['has_rate']" @click="saleStatusChange(trip)">{{ trip['sale_status'] }}</span>
                        <span class="text-red" v-else><IconExclamation :class="'h-1em inline'"/> Тариф не задан</span>
                    </base-table-cell-item>
                </base-table-cell>
                <base-table-cell :nowrap="true" :class="'right'">
                    <button-icon v-if="trip['chained']" :class="'mr-5'" :color="'blue'" @click="chainInfo(trip)">
                        <icon-link/>
                    </button-icon>
                    <actions-menu :title="null">
                        <span class="link">Редактировать</span>
                        <span class="link">Копировать рейс</span>
                        <span class="link">Удалить</span>
                    </actions-menu>
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

import DictionaryDropDown from "@/Components/Inputs/DictionaryDropDown";

import ListPage from "../../../../Layouts/ListPage";
import PageTitleBar from "../../../../Layouts/Parts/PageTitleBar";
import ActionsMenu from "../../../../Components/GUI/GuiActionsMenu";
import PageBarItem from "../../../../Layouts/Parts/PageBarItem";
import Message from "@/Components/GUI/GuiMessage";
import BasePagination from "../../../../Components/GUI/GuiPagination";

import BaseInput from "../../../../Components/Base/BaseInput";
import PopUp from "../../../../Components/PopUp";
import BaseDateInput from "../../../../Components/Base/BaseDateInput";
import IconBackward from "@/Components/Icons/IconBackward";
import IconForward from "@/Components/Icons/IconForward";
import ButtonIcon from "@/Components/GUI/GuiIconButton";
import moment from "moment";
import IconLink from "@/Components/Icons/IconLink";
import IconExclamation from "@/Components/Icons/IconExclamation";

export default {
    components: {
        IconExclamation,
        IconLink,
        ButtonIcon,
        IconForward,
        IconBackward,
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
        setDay(increment) {
            let date = moment(this.list.filters['date'], 'DD.MM.YYYY');
            this.list.filters['date'] = date.date(date.date() + increment).format('DD.MM.YYYY');
            this.reload();
        },
        chainInfo(trip) {
            axios.post('/api/trips/info', {id: trip['id']})
                .then(response => {
                    let date_from = response.data.data['date_from'];
                    let date_to = response.data.data['date_to'];
                    let count = response.data.data['count'];
                    let message = 'Рейс имеет связанные рейсы с аналогичными параметрами в диапазоне: <b>' + date_from + ' - ' + date_to + '</b>';
                    message += '<br/>';
                    message += '<br/>';
                    message += 'Количество связанных рейсов: <b>' + count + '</b>';
                    this.$dialog.show(message, 'info', 'green', [this.$dialog.button('ok', 'ok', 'green')], 'center');
                })
                .catch(error => {
                    this.$toast.error(error.response.data.message, 5000);
                })
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
