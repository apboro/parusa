<template>
    <loading-progress :loading="processing">
        <container mt-15 mb-15 border p-20>
            <container inline mr-50>
                <heading text-md mb-10>Баланс лицевого счёта</heading>
                <heading>{{ list.payload['balance'] }} руб.</heading>
            </container>
            <container inline>
                <heading text-md mb-10>Доступный остаток по лицевому счёту</heading>
                <heading>{{ list.payload['limit'] }} руб.</heading>
                <span class="link text-sm flex mt-10" v-if="editable">Изменить</span>
            </container>
        </container>

        <div class="page__body-bar">
            <div class="page__body-bar-filters">
                <page-bar-item :title="'Период'">
                    <base-date-input
                        v-model="list.filters['date_from']"
                        :original="list.filters_original['date_from']"
                        @changed="reload"
                    />
                    <base-date-input
                        v-model="list.filters['date_to']"
                        :original="list.filters_original['date_to']"
                        @changed="reload"
                    />
                    <actions-menu :title="null"/>
                </page-bar-item>
                <page-bar-item :title="'Тип операции'">
                    <dictionary-drop-down
                        :dictionary="'transaction_primary_types'"
                        v-model="list.filters['transaction_type_id']"
                        :placeholder="'Все'"
                        :has-null="true"
                        :original="list.filters_original['transaction_type_id']"
                        @changed="reload"
                    />
                </page-bar-item>
            </div>
            <div class="page__body-bar-search">
                <actions-menu :class="'self-align-end'" :title="'Операции'">
                    <span class="link">Пополнение счёта</span>
                </actions-menu>
            </div>
        </div>

        <container>
            <base-table v-if="!empty(list.data)">
                <template v-slot:header>
                    <base-table-head :header="list.titles"/>
                </template>
                <base-table-row v-for="(transaction, key) in list.data" :key="key">
                    <base-table-cell>{{ transaction['date'] }}</base-table-cell>
                    <base-table-cell>{{ transaction['type'] }}</base-table-cell>
                    <base-table-cell :class="{'text-dark-green': transaction['sign'] === 1, 'text-dark-red': transaction['sign'] === -1}"
                    >{{ transaction['sign'] === -1 ? '-' : '+' }} {{ transaction['amount'] }} руб.
                    </base-table-cell>
                    <base-table-cell>{{ transaction['reason'] }}</base-table-cell>
                    <base-table-cell>{{ transaction['operator'] }}</base-table-cell>
                </base-table-row>
            </base-table>
            <message v-else-if="list.loaded">Нет операций за выбранный период</message>
            <base-pagination :pagination="list.pagination" @pagination="setPagination"/>
        </container>

        <container mt-50>
            <heading text-lg>Статистика по счёту за период <span class="bold">{{ list.filters['date_from'] }} - {{ list.filters['date_to'] }}</span></heading>
            <heading text-md mt-20>Состояние лицевого счёта:</heading>
            <container inline pr-40 pt-15>
                <value :class="'w-300px'" :title="'На начало периода'">{{ list.payload['period_start_amount'] }} руб</value>
                <value :class="'w-300px'" :title="'На конец периода'">{{ list.payload['period_end_amount'] }} руб</value>
                <value :class="'w-300px'" :title="'Сальдо'">{{ list.payload['period_income_amount'] }} руб</value>
            </container>
            <container inline pr-40 pt-15>
                <value :class="'w-300px'" :title="'Сумма продаж'">{{ list.payload['period_sell_amount'] }} руб</value>
                <value :class="'w-300px'" :title="'Начислено комиссионных'">{{ list.payload['period_commission_amount'] }} руб</value>
                <value :class="'w-300px'" :title="'Состав продаж, заказы/билеты'">{{ list.payload['period_sell_orders'] }} / {{ list.payload['period_sell_tickets'] }}</value>
            </container>
        </container>
    </loading-progress>
</template>

<script>
import LoadingProgress from "@/Components/LoadingProgress";
import Container from "@/Components/GUI/Container";
import ActionsMenu from "@/Components/ActionsMenu";
import PageBarItem from "@/Layouts/Parts/PageBarItem";
import Heading from "@/Components/GUI/Heading";
import BaseDateInput from "@/Components/Base/BaseDateInput";
import ValueArea from "@/Components/GUI/ValueArea";
import Value from "@/Components/GUI/Value";
import DictionaryDropDown from "@/Components/Dictionary/DictionaryDropDown";
import listDataSource from "@/Helpers/Core/listDataSource";
import UseBaseTableBundle from "@/Mixins/UseBaseTableBundle";
import empty from "@/Mixins/empty";
import Message from "@/Layouts/Parts/Message";
import BasePagination from "@/Components/Base/BasePagination";

export default {
    props: {
        partnerId: {type: Number, required: true},
        editable: {type: Boolean, default: false},
    },

    mixins: [UseBaseTableBundle, empty],

    components: {
        BasePagination,
        Message,
        DictionaryDropDown,
        Value,
        ValueArea,
        BaseDateInput,
        Heading,
        PageBarItem,
        ActionsMenu,
        Container,
        LoadingProgress,
    },

    data: () => ({
        list: null,
    }),

    computed: {
        processing() {
            return this.list.loading;
        },
    },

    created() {
        this.list = listDataSource('/api/account', true, {id: this.partnerId});
        this.list.load(1, null, true);
    },

    methods: {
        reload() {
            this.list.load();
        },
        setPagination(page, perPage) {
            this.list.load(page, perPage);
        },
    }
}
</script>
