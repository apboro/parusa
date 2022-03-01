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
                <LayoutFiltersItem :title="'Поиск по номеру заказа, билета'">
                    <InputSearch v-model="list.search" @change="list.load()"/>
                </LayoutFiltersItem>
            </template>
        </LayoutFilters>

        <ListTable v-if="list.list && list.list.length > 0" :titles="list.titles" :has-action="true">
            <template v-for="order in list.list">
                <ListTableRow :no-odd-even="true">
                    <ListTableCell>
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
                                <td v-for="(cell, key) in ['№ билета', 'Отправление', 'Экскурсия, причал', 'Тип билета', 'Стоимость', 'Статус билета']" :key="key"
                                >{{ cell }}
                                </td>
                            </tr>
                            </thead>
                            <tr v-for="(ticket, key) in order['tickets']" :key="key">
                                <td class="p-5">
                                    <router-link class="link" :to="{name: 'ticket-info', params: {id: ticket['id']}}" v-html="highlight(ticket['id'])"/>
                                </td>
                                <td class="p-5">
                                    <div>{{ ticket['trip_date'] }}</div>
                                    <div>{{ ticket['trip_time'] }}</div>
                                </td>
                                <td class="p-5">
                                    <div>{{ ticket['excursion'] }}</div>
                                    <div>{{ ticket['pier'] }}</div>
                                </td>
                                <td class="p-5">{{ ticket['type'] }}</td>
                                <td class="p-5">{{ ticket['amount'] }} руб.</td>
                                <td class="p-5">
                                    <div>{{ ticket['status'] }}</div>
                                    <div v-if="ticket['used']">Использован {{ ticket['used'] }}</div>
                                </td>
                            </tr>
                        </table>
                    </ListTableCell>
                </ListTableRow>
            </template>
        </ListTable>

        <GuiMessage v-else-if="list.is_loaded">Ничего не найдено</GuiMessage>

        <GuiPagination :pagination="list.pagination" @pagination="(page, per_page) => list.load(page, per_page)"/>

        <PopUp :title="'Информация о заказе'" ref="info" :close-on-overlay="true">
            <template v-if="info">
                <GuiValue :title="'Имя'">{{ info['buyer_name'] }}</GuiValue>
                <GuiValue :title="'Email'">{{ info['buyer_email'] }}</GuiValue>
                <GuiValue :title="'Телефон'">{{ info['buyer_phone'] }}</GuiValue>
                <GuiValue :title="'Способ продажи'">{{ info['order_type'] }}</GuiValue>
                <GuiValue :dots="info['position_name'] !== null" :title="'Партнёр'">{{ info['partner'] }}</GuiValue>
                <GuiValue v-if="info['position_name'] !== null" :dots="false" :title="'Продавец'">{{ info['position_name'] }}</GuiValue>
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
import GuiPagination from "@/Components/GUI/GuiPagination";
import PopUp from "@/Components/PopUp";
import GuiValue from "@/Components/GUI/GuiValue";
import InputDate from "@/Components/Inputs/InputDate";

export default {
    props: {
        partnerId: {type: Number, default: null},
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
        GuiPagination,
        PopUp,
        GuiValue,
    },

    data: () => ({
        list: null,
        info: null,
    }),

    created() {
        this.list = list('/api/registries/orders', {partner_id: this.partnerId});
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
