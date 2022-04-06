<template>
    <LayoutPage :title="$route.meta['title']">
        <GuiContainer w-500px flex mt-20 mb-20>
            <GuiText mr-10 flex items-center>Поиск</GuiText>
            <div class="inline grow">
                <InputSearch v-model="list.search"
                             :placeholder="'Введите номер брони'"
                             @search="search"
                />
            </div>
            <GuiButton :class="'ml-15'" @click="search" :disabled="list.search === null">Найти</GuiButton>
        </GuiContainer>

        <LoadingProgress :loading="processing">
            <ListTable v-if="list.list && list.list.length > 0" :titles="list.titles">
                <template v-for="order in list.list">
                    <ListTableRow :no-odd-even="true" :no-highlight="true">
                        <ListTableCell :class="'bold'">
                            <router-link class="link" :to="{name: 'reserve', params: {id: order['id']}}" v-html="highlight(order['id'])"/>
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
                        <ListTableCell>
                            {{ order['valid_before'] }}
                        </ListTableCell>
                    </ListTableRow>
                    <ListTableRow :no-odd-even="true" :no-highlight="true">
                        <ListTableCell colspan="7">
                            <table class="details-table">
                                <thead>
                                <tr>
                                    <th v-for="(cell, key) in ['№ билета', 'Отправление', 'Экскурсия, причал', 'Тип билета', 'Стоимость', 'Статус билета']" :key="key"
                                    >{{ cell }}
                                    </th>
                                </tr>
                                </thead>
                                <tr v-for="(ticket, key) in order['tickets']" :key="key">
                                    <td>
                                        {{ ticket['id'] }}
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
        list: list('/api/registries/reserves'),
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
            this.$router.push({name: 'reserve', params: {id: order['id']}});
        },

        highlight(text) {
            return this.$highlight(String(text), String(this.list.search), true);
        },
    }
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
