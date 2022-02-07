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

        <base-table v-if="!empty(list.data)">
            <template v-slot:header>
                <base-table-head
                    :header="['Дата и время продажи', '№ заказа, билета', 'Тип билета, стоимость', 'Комиссия, руб.', 'Рейс', 'Способ продажи', 'Продавец', 'Возврат']"/>
            </template>
            <base-table-row v-for="(ticket, key) in list.data" :key="key">
                <base-table-cell>
                    <base-table-cell-item>{{ ticket['date'] }}</base-table-cell-item>
                    <base-table-cell-item>{{ ticket['time'] }}</base-table-cell-item>
                </base-table-cell>
                <base-table-cell>
                    <base-table-cell-item><span class="link">{{ ticket['order_number'] }}</span></base-table-cell-item>
                    <base-table-cell-item>{{ ticket['number'] }}</base-table-cell-item>
                </base-table-cell>
                <base-table-cell>
                    <base-table-cell-item>{{ ticket['type'] }}</base-table-cell-item>
                    <base-table-cell-item>{{ ticket['amount'] }}</base-table-cell-item>
                </base-table-cell>
                <base-table-cell>
                    <base-table-cell-item>{{ ticket['commission'] }}</base-table-cell-item>
                    <base-table-cell-item>{{ ticket['commission_amount'] }}</base-table-cell-item>
                </base-table-cell>
                <base-table-cell>
                    <base-table-cell-item>{{ ticket['trip_date'] }} (№{{ ticket['trip_number'] }})</base-table-cell-item>
                    <base-table-cell-item>{{ ticket['trip_time'] }}, {{ ticket['pier'] }}, {{ ticket['excursion'] }}</base-table-cell-item>
                </base-table-cell>
                <base-table-cell>{{ ticket['sale_way'] }}</base-table-cell>
                <base-table-cell>{{ ticket['sale_by'] }}</base-table-cell>
                <base-table-cell>
                    <template v-if="ticket['return_up_to']">
                        <base-table-cell-item><span class="link">Оформить возврат</span></base-table-cell-item>
                        <base-table-cell-item>до {{ ticket['return_up_to'] }}</base-table-cell-item>
                    </template>
                    <span v-else>—</span>
                </base-table-cell>
            </base-table-row>
        </base-table>
        <message v-else-if="list.loaded">Ничего не найдено</message>
    </loading-progress>
    <base-pagination :pagination="list.pagination" @pagination="setPagination"/>
</template>

<script>
import UseBaseTableBundle from "@/Mixins/UseBaseTableBundle";
import listDataSource from "@/Helpers/Core/listDataSource";
import empty from "@/Mixins/empty";
import LoadingProgress from "@/Components/LoadingProgress";
import Message from "@/Components/GUI/GuiMessage";
import BasePagination from "@/Components/GUI/GuiPagination";

export default {
    components: {
        BasePagination,
        Message,
        LoadingProgress,

    },

    mixins: [UseBaseTableBundle, empty],

    data: () => ({
        list: null,
    }),

    created() {
        this.list = listDataSource('/api/registries/tickets');
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
