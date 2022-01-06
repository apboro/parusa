<template>
    <loading-progress :loading="list.loading">
        <!--            <page-bar-item :title="'Статус причала'">-->
        <!--                <dictionary-drop-down-->
        <!--                    :dictionary="'pier_statuses'"-->
        <!--                    :placeholder="'Все'"-->
        <!--                    :has-null="true"-->
        <!--                    :original="list.filters_original.status_id"-->
        <!--                    v-model="list.filters.status_id"-->
        <!--                    @changed="reload"-->
        <!--                />-->
        <!--            </page-bar-item>-->

        <base-table v-if="!empty(list.data)" :highlight="false">
            <template v-slot:header>
                <base-table-head :has-actions="true" :header="['№ заказа', 'Дата оплаты заказа', 'Информация о заказе', 'Билетов в заказе', 'Сумма заказа, руб.']"/>
            </template>
            <template v-for="(order, key) in list.data" :key="key">
                <base-table-row>
                    <base-table-cell><span class="link">{{ order['id'] }}</span></base-table-cell>
                    <base-table-cell>{{ order['date'] }}</base-table-cell>
                    <base-table-cell><span class="link">Информация</span></base-table-cell>
                    <base-table-cell>{{ order['tickets_count'] }}</base-table-cell>
                    <base-table-cell>{{ order['amount'] }}</base-table-cell>
                    <base-table-cell :class="'pv-5'">
                        <expand @expand="expandTickets($event, order)"/>
                    </base-table-cell>
                </base-table-row>
                <base-table-row v-if="order['show_tickets']" :class="'base-table__row-skip-hl'">
                    <base-table-cell colspan="6" class="p-0">
                        <loading-progress :loading="order['loading_tickets']">

                            <table class="details-table" v-if="!empty(order['tickets'])">
                                <thead>
                                <tr>
                                    <td v-for="(cell, key) in ['№ билета', 'Отправление', 'Экскурсия, причал', 'Тип билета', 'Стоимость, руб.', 'Статус билета']" :key="key"
                                    >{{ cell }}
                                    </td>
                                </tr>
                                </thead>
                                <tr v-for="(ticket, key) in order['tickets']" :key="key">
                                    <td class="p-5">{{ ticket['id'] }}</td>
                                    <td class="p-5">
                                        <div>{{ ticket['trip_date'] }}</div>
                                        <div>{{ ticket['trip_time'] }}</div>
                                    </td>
                                    <td class="p-5">
                                        <div>{{ ticket['excursion'] }}</div>
                                        <div>{{ ticket['pier'] }}</div>
                                    </td>
                                    <td class="p-5">{{ ticket['type'] }}</td>
                                    <td class="p-5">{{ ticket['amount'] }}</td>
                                    <td class="p-5">
                                        <div>{{ ticket['status'] }}</div>
                                        <div v-if="ticket['used']">Использован {{ ticket['used'] }}</div>
                                    </td>
                                </tr>
                            </table>

                        </loading-progress>
                    </base-table-cell>
                </base-table-row>
            </template>
        </base-table>
        <message v-else-if="list.loaded">Ничего не найдено</message>
    </loading-progress>
    <base-pagination :pagination="list.pagination" @pagination="setPagination"/>
</template>

<script>
import UseBaseTableBundle from "../../Mixins/UseBaseTableBundle";
import empty from "../../Mixins/empty";
import listDataSource from "../../Helpers/Core/listDataSource";

import LoadingProgress from "../../Components/LoadingProgress";
import Message from "../../Layouts/Parts/Message";
import BasePagination from "../../Components/Base/BasePagination";
import Expand from "../../Components/Expand";

export default {
    components: {
        Expand,
        BasePagination,
        Message,
        LoadingProgress,

    },

    mixins: [UseBaseTableBundle, empty],

    data: () => ({
        list: null,
    }),

    created() {
        this.list = listDataSource('/api/registries/orders');
        this.list.load(1, null, true);
    },

    methods: {
        setPagination(page, perPage) {
            this.list.load(page, perPage);
        },
        reload() {
            this.list.load();
        },
        expandTickets(expanded, order) {
            if (expanded) {
                order['show_tickets'] = true;
                order['loading_tickets'] = true;
                axios.post('/api/registries/orders/tickets', {})
                    .then(response => {
                        order['tickets'] = response.data.list;
                    })
                    .catch(error => {
                        order['show_tickets'] = false;
                        this.$toast.error(error.response.data.message);
                    })
                    .finally(() => {
                        order['loading_tickets'] = false;
                    });
            } else {
                order['show_tickets'] = false;
            }
        }
    },
}
</script>
