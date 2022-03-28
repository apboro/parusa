<template>
    <LayoutPage :title="$route.meta['title']">
        <GuiText mt-20 mb-10>Поиск</GuiText>
        <GuiContainer w-500px flex mb-20>
            <div class="inline grow">
                <InputSearch v-model="list.search"
                             :placeholder="'Введите номер заказа или билета'"
                             @search="search"
                />
            </div>
            <GuiButton :class="'ml-15'" @click="search" :disabled="list.search === null">Найти</GuiButton>
        </GuiContainer>

        <LoadingProgress :loading="processing">
            <ListTable v-if="list.list && list.list.length > 0" :titles="list.titles" :has-action="true">
                <template v-for="order in list.list">
                    <ListTableRow :no-odd-even="true" :no-highlight="true">
                        <ListTableCell :class="'bold'">
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
                            <GuiButton @clicked="makeReturn(order)" :disabled="!order['returnable']">Оформить возврат</GuiButton>
                        </ListTableCell>
                    </ListTableRow>
                    <ListTableRow :no-odd-even="true" :no-highlight="true">
                        <ListTableCell colspan="6">
                            <table class="details-table">
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

            <Pagination v-if="list.is_loaded" :pagination="list.pagination" @pagination="(page, per_page) => list.load(page, per_page)"/>
        </LoadingProgress>

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
    </LayoutPage>
</template>

<script>
import LayoutPage from "@/Components/Layout/LayoutPage";
import GuiContainer from "@/Components/GUI/GuiContainer";
import InputSearch from "@/Components/Inputs/InputSearch";
import list from "@/Core/List";
import GuiText from "@/Components/GUI/GuiText";
import GuiButton from "@/Components/GUI/GuiButton";
import LoadingProgress from "@/Components/LoadingProgress";
import ListTable from "@/Components/ListTable/ListTable";
import ListTableRow from "@/Components/ListTable/ListTableRow";
import ListTableCell from "@/Components/ListTable/ListTableCell";
import GuiExpand from "@/Components/GUI/GuiExpand";
import GuiMessage from "@/Components/GUI/GuiMessage";
import Pagination from "@/Components/Pagination";
import PopUp from "@/Components/PopUp";
import GuiValue from "@/Components/GUI/GuiValue";

export default {
    components: {
        GuiValue,
        PopUp,
        Pagination,
        GuiMessage,
        GuiExpand,
        ListTableCell,
        ListTableRow,
        ListTable,
        LoadingProgress,
        GuiButton,
        GuiText,
        InputSearch,
        GuiContainer,
        LayoutPage,
    },

    computed: {
        processing() {
            return false;
        }
    },

    data: () => ({
        list: list('/api/registries/orders'),
        info: null,
    }),

    methods: {
        search() {
            if (this.list.search !== null) {
                this.list.load();
            }
        },

        showInfo(order) {
            this.info = order['info'];
            this.$refs.info.show()
                .then(() => {
                    this.info = null;
                });
        },

        makeReturn(order) {
            this.$router.push({name: 'order-info', params: {id: order['id']}, query: {return: 1}});
        },

        highlight(text) {
            return this.$highlight(String(text), String(this.list.search), true);
        },
    }
}
</script>
