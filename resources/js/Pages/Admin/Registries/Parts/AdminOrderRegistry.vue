<template>
    <LoadingProgress :loading="list.is_loading">

        <LayoutFilters>
            <LayoutFiltersItem :title="'Период'">
                <InputDate
                    v-model="list.filters['date_from']"
                    :original="list.filters_original['date_from']"
                    @change="list.load()"
                />
                <InputDate
                    v-model="list.filters['date_to']"
                    :original="list.filters_original['date_to']"
                    @change="list.load()"
                />
            </LayoutFiltersItem>
            <LayoutFiltersItem :title="'Способ продажи'">
                <DictionaryDropDown
                    :dictionary="'order_types'"
                    v-model="list.filters['order_type_id']"
                    :original="list.filters_original['order_type_id']"
                    :placeholder="'Все'"
                    :has-null="true"
                    :small="true"
                    @change="list.load()"
                />
            </LayoutFiltersItem>
            <template #search>
                <LayoutFiltersItem :title="'Поиск заказа/билета по номеру, имени, email, телефону покупателя'">
                    <InputSearch v-model="list.search" @change="list.load()"/>
                </LayoutFiltersItem>
            </template>
        </LayoutFilters>

        <ListTable v-if="list.list && list.list.length > 0" :titles="list.titles" :has-action="true">
            <template v-for="order in list.list">
                <ListTableRow :no-odd-even="true">
                    <ListTableCell class="bold">
                        <router-link class="link" :to="{name: 'order-info', params: {id: order['id']}}" v-html="highlight(order['id'])"/>
                    </ListTableCell>
                    <ListTableCell>
                        {{ order['date'] }}
                    </ListTableCell>
                    <ListTableCell>
                        <span class="link" @click="showInfo(order)">Информация</span>
                    </ListTableCell>
                    <ListTableCell>
                        {{ order['tickets_total'] }}
                    </ListTableCell>
                    <ListTableCell>
                        {{ order['amount'] }} руб.
                    </ListTableCell>
                    <ListTableCell style="padding-top: 5px; padding-bottom: 5px">
                        <GuiExpand @expand="expandTickets(order)"/>
                    </ListTableCell>
                </ListTableRow>
                <ListTableRow v-if="order['show_tickets']" :no-odd-even="true" :no-highlight="true">
                    <ListTableCell colspan="6">
                        <table class="details-table" v-if="order['show_tickets']">
                            <thead>
                            <tr>
                                <th v-for="(cell, key) in ['№ билета', 'Отправление', 'Экскурсия, причал', 'Тип билета', 'Стоимость', 'Статус билета']" :key="key"
                                >{{ cell }}
                                </th>
                            </tr>
                            </thead>
                            <tr v-for="(ticket, key) in order['tickets']" :key="key">
                                <td>
                                    <router-link class="link" :to="{name: 'ticket-info', params: {id: ticket['id']}}" v-html="highlight(ticket['id'])"/>
                                </td>
                                <td>
                                    <div>{{ ticket['trip_date'] }}</div>
                                    <div>{{ ticket['trip_time'] }}</div>
                                </td>
                                <td>
                                    <div>{{ ticket['excursion'] }}</div>
                                    <div>{{ ticket['pier'] }}</div>
                                </td>
                                <td>{{ ticket['type'] }}</td>
                                <td>{{ ticket['amount'] }} руб.</td>
                                <td>
                                    <div>{{ ticket['status'] }}</div>
                                    <div v-if="ticket['used']">Использован {{ ticket['used'] }}</div>
                                </td>
                            </tr>
                        </table>
                    </ListTableCell>
                </ListTableRow>
            </template>
        </ListTable>

        <GuiMessage border v-else-if="list.is_loaded">Ничего не найдено</GuiMessage>

        <Pagination :pagination="list.pagination" @pagination="(page, per_page) => list.load(page, per_page)"/>

        <PopUp :title="'Информация о заказе'" ref="info" :close-on-overlay="true">
            <template v-if="info">
                <GuiValue :title="'Имя'">{{ info['buyer_name'] }}</GuiValue>
                <GuiValue :title="'Email'">{{ info['buyer_email'] }}</GuiValue>
                <GuiValue :title="'Телефон'">{{ info['buyer_phone'] }}</GuiValue>
                <GuiValue :title="'Способ продажи'">{{ info['order_type'] }}</GuiValue>
                <GuiValue v-if="info['terminal_name']" :title="'Касса'">{{ info['terminal_name'] }} <span v-if="info['cashier']">({{ info['cashier'] }})</span></GuiValue>
                <GuiValue :dots="false" :title="info['position_name'] === null ? 'Промоутер' : 'Партнёр'">{{ info['partner'] }} <span v-if="info['position_name']">({{ info['position_name'] }})</span></GuiValue>
            </template>
        </PopUp>

    </LoadingProgress>
</template>

<script>
import list from "@/Core/List";
import LoadingProgress from "@/Components/LoadingProgress";
import LayoutFilters from "@/Components/Layout/LayoutFilters";
import LayoutFiltersItem from "@/Components/Layout/LayoutFiltersItem";
import DictionaryDropDown from "@/Components/Inputs/DictionaryDropDown";
import InputSearch from "@/Components/Inputs/InputSearch";
import ListTable from "@/Components/ListTable/ListTable";
import ListTableRow from "@/Components/ListTable/ListTableRow";
import ListTableCell from "@/Components/ListTable/ListTableCell";
import GuiExpand from "@/Components/GUI/GuiExpand";
import GuiMessage from "@/Components/GUI/GuiMessage";
import Pagination from "@/Components/Pagination";
import PopUp from "@/Components/PopUp";
import GuiValue from "@/Components/GUI/GuiValue";
import InputDate from "@/Components/Inputs/InputDate";

export default {
    props: {
        partnerId: {type: Number, default: null},
        terminalId: {type: Number, default: null},
    },

    components: {
        InputDate,
        LoadingProgress,
        LayoutFilters,
        LayoutFiltersItem,
        DictionaryDropDown,
        InputSearch,
        ListTable,
        ListTableRow,
        ListTableCell,
        GuiExpand,
        GuiMessage,
        Pagination,
        PopUp,
        GuiValue,
    },

    data: () => ({
        list: null,
        info: null,
    }),

    created() {
        this.list = list('/api/registries/orders', {partner_id: this.partnerId, terminal_id: this.terminalId});
        this.list.initial();
    },

    methods: {
        showInfo(order) {
            this.info = order['info'];
            this.$refs.info.show()
                .then(() => {
                    this.info = null;
                });
        },

        expandTickets(order) {
            order['show_tickets'] = !order['show_tickets'];
        },

        highlight(text) {
            return this.$highlight(String(text), String(this.list.search), true);
        },
    },
}
</script>

<style lang="scss" scoped>
$project_font: -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica Neue, Arial, Noto Sans, sans-serif, Apple Color Emoji, Segoe UI Emoji, Segoe UI Symbol, Noto Color Emoji !default;

.details-table {
    width: calc(100% - 40px);
    margin: 0 0 20px 40px;
    border-collapse: collapse;
    font-size: 14px;

    & > thead > tr > th {
        padding: 10px;
        font-family: $project_font;
        color: #727272;
        text-align: left;
        font-weight: normal;
    }

    & > tr {
        border-top: 1px solid #e3e3e3;

        &:hover {
            background-color: #f7f7f7;
        }

        & > td {
            padding: 10px;
            color: #3e3e3e;

            & > *:not(:last-child) {
                margin-bottom: 5px;
            }
        }
    }
}
</style>
