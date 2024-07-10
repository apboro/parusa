<template>
    <LoadingProgress :loading="this.list.is_loading">
        <GuiContainer mt-15 mb-15 border p-20>
            <GuiContainer inline mr-50>
                <GuiHeading text-md mb-10>Сумма билетов проданных на личной кассе</GuiHeading>
                <GuiHeading>{{ list.payload['sumSoldTickets'] }} руб.</GuiHeading>
            </GuiContainer>
            <GuiContainer inline mr-50>
                <GuiHeading text-md mb-10>Заработок промоутера с билетов на личной кассе</GuiHeading>
                <GuiHeading>{{ list.payload['sumCommissionSelfSold'] }} руб.</GuiHeading>
            </GuiContainer>
            <GuiContainer inline mr-50>
                <GuiHeading text-md mb-10>Сумма для передачи в кассу</GuiHeading>
                <GuiHeading>{{ list.payload['payToBase'] }} руб.</GuiHeading>
            </GuiContainer>
        </GuiContainer>

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
                        :from="list.filters['date_from']"
                        @change="list.load()"
                    />
                </LayoutFiltersItem>
                <LayoutFiltersItem :title="'Тип операции'">
                    <DictionaryDropDown
                        v-model="list.filters['transaction_type_id']"
                        :dictionary="'transaction_primary_types'"
                        :original="list.filters_original['transaction_type_id']"
                        :placeholder="'Все'"
                        :has-null="true"
                        @change="list.load()"
                    />
                </LayoutFiltersItem>
            </LayoutFilters>

            <GuiContainer w-100>
                <ListTable v-if="list.list.length > 0" :titles="list.titles">
                    <ListTableRow v-for="transaction in list.list">
                        <ListTableCell>
                            {{ transaction['timestamp'] }}
                        </ListTableCell>
                        <ListTableCell>
                            {{ transaction['type'] }}
                        </ListTableCell>
                        <ListTableCell :nowrap="true"
                                       :class="{'text-dark-green': transaction['sign'] === 1, 'text-dark-red': transaction['sign'] === -1}">
                            {{ transaction['sign'] === -1 ? '-' : '+' }} {{ transaction['amount'] }} руб.
                        </ListTableCell>
                        <ListTableCell>
                            <template v-if="transaction['reason_raw']">
                                {{ transaction['reason_raw']['title'] }}
                                <template v-if="transaction['reason_raw']['object'] === 'order'">
                                    <router-link v-if="transaction['reason_raw']['object_id']" class="link"
                                                 :to="{name: 'order-info', params: {id: transaction['reason_raw']['object_id'] }}">
                                        {{ transaction['reason_raw']['caption'] }}
                                    </router-link>
                                    <template v-else>
                                        {{ transaction['reason_raw']['caption'] }}
                                    </template>
                                </template>
                                <template v-else-if="transaction['reason_raw']['object'] === 'ticket'">
                                    <router-link v-if="transaction['reason_raw']['object_id']" class="link"
                                                 :to="{name: 'ticket-info', params: {id: transaction['reason_raw']['object_id'] }}">
                                        {{ transaction['reason_raw']['caption'] }}
                                    </router-link>
                                    <template v-else>
                                        {{ transaction['reason_raw']['caption'] }}
                                    </template>
                                </template>
                            </template>
                            <GuiHint v-if="transaction['comments']" mt-10>{{ transaction['comments'] }}</GuiHint>
                        </ListTableCell>
                        <ListTableCell :nowrap="true">
                            {{ transaction['operator'] }}
                        </ListTableCell>
                    </ListTableRow>
                </ListTable>
                <GuiMessage v-else-if="list.is_loaded">Нет операций за выбранный период</GuiMessage>
                <Pagination :pagination="list.pagination" @pagination="(page, per_page) => list.load(page, per_page)"/>
            </GuiContainer>


            <GuiContainer mt-30>
                <GuiValue :class="'w-300px'" :title="'Сумма по выборке'">{{ list.payload['selected_total'] }} руб.
                </GuiValue>
                <GuiValue :class="'w-300px'" :title="'Сумма на странице'">{{ list.payload['selected_page_total'] }}
                    руб.
                </GuiValue>
            </GuiContainer>

            <GuiContainer mt-30>
                <GuiHeading text-lg>Статистика по счёту за период <span class="bold">{{ list.payload['date_from'] }} - {{
                        list.payload['date_to']
                    }}</span></GuiHeading>
                <GuiHeading text-md mt-20>Состояние лицевого счёта:</GuiHeading>
                <GuiContainer inline pr-40 pt-15>
                    <GuiValue :class="'w-300px'" :title="'На начало периода'">{{ list.payload['period_start_amount'] }}
                        руб.
                    </GuiValue>
                    <GuiValue :class="'w-300px'" :title="'На конец периода'">{{ list.payload['period_end_amount'] }}
                        руб.
                    </GuiValue>
                    <GuiValue :class="'w-300px'" :title="'Сальдо'">{{ list.payload['period_income_amount'] }} руб.
                    </GuiValue>
                </GuiContainer>
                <!--
                <GuiContainer inline pr-40 pt-15>
                    <GuiValue :class="'w-300px'" :title="'Сумма продаж'">{{ list.payload['period_sell_amount'] }} руб.</GuiValue>
                    <GuiValue :class="'w-300px'" :title="'Начислено комиссионных'">{{ list.payload['period_commission_amount'] }} руб.</GuiValue>
                    <GuiValue :class="'w-300px'" :title="'Состав продаж, заказы/билеты'">{{ list.payload['period_sell_orders'] }} / {{ list.payload['period_sell_tickets'] }}</GuiValue>
                </GuiContainer>
                -->
            </GuiContainer>
    </LoadingProgress>
</template>

<script>
import list from "@/Core/List";
import LoadingProgress from "@/Components/LoadingProgress";
import GuiContainer from "@/Components/GUI/GuiContainer";
import GuiHeading from "@/Components/GUI/GuiHeading";
import LayoutFilters from "@/Components/Layout/LayoutFilters";
import LayoutFiltersItem from "@/Components/Layout/LayoutFiltersItem";
import InputDate from "@/Components/Inputs/InputDate";
import DictionaryDropDown from "@/Components/Inputs/DictionaryDropDown";
import ListTable from "@/Components/ListTable/ListTable";
import GuiMessage from "@/Components/GUI/GuiMessage";
import Pagination from "@/Components/Pagination";
import ListTableRow from "@/Components/ListTable/ListTableRow";
import ListTableCell from "@/Components/ListTable/ListTableCell";
import GuiHint from "@/Components/GUI/GuiHint";
import GuiValue from "@/Components/GUI/GuiValue";

export default {

    props: {
        partnerId: {type: Number, default: null},
    },

    components: {
        DictionaryDropDown,
        InputDate,
        GuiValue,
        GuiHint,
        ListTableCell,
        ListTableRow,
        Pagination,
        GuiMessage,
        ListTable,
        LayoutFiltersItem,
        LayoutFilters,
        GuiHeading,
        GuiContainer,
        LoadingProgress

    },

    data: () => ({
        list: null,
    }),

    created() {
        this.list = list('/api/account', {id: this.partnerId}),
            this.list.initial();
    },
}
</script>
