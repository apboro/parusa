<template>
    <LoadingProgress :loading="list.is_loading">

        <LayoutFilters>
            <LayoutFiltersItem :title="'Период'">
                <base-date-input
                    v-model="list.filters['date_from']"
                    :original="list.filters_original['date_from']"
                    @changed="list.load()"
                />
                <base-date-input
                    v-model="list.filters['date_to']"
                    :original="list.filters_original['date_to']"
                    @changed="list.load()"
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

        <ListTable v-if="list.list && list.list.length > 0" :titles="list.titles">
            <ListTableRow v-for="ticket in list.list">
                <ListTableCell>
                    <div>{{ ticket['date'] }}</div>
                    <div>{{ ticket['time'] }}</div>
                </ListTableCell>
                <ListTableCell>
                    <div v-html="highlight(ticket['order_id'])"></div>
                    <div v-html="highlight(ticket['id'])"></div>
                </ListTableCell>
                <ListTableCell>
                    <div>{{ ticket['type'] }}</div>
                    <div>{{ ticket['amount'] }} руб.</div>
                </ListTableCell>
                <ListTableCell>
                    <template v-if="ticket['commission_amount']">
                        <div>{{ ticket['commission_type'] }}</div>
                        <div>{{ ticket['commission_amount'] }} руб.</div>
                    </template>
                    <span v-else>—</span>
                </ListTableCell>
                <ListTableCell>
                    <div><b>№{{ ticket['trip_id'] }}</b> {{ ticket['trip_date'] }} {{ ticket['trip_time'] }}</div>
                    <div>{{ ticket['excursion'] }} {{ ticket['pier'] }}</div>
                </ListTableCell>
                <ListTableCell>
                    {{ ticket['order_type'] }}
                </ListTableCell>
                <ListTableCell>
                    <div>{{ ticket['partner'] }}</div>
                    <div>{{ ticket['sale_by'] }}</div>
                </ListTableCell>
                <ListTableCell>
                    {{ ticket['status'] }}
                </ListTableCell>
                <ListTableCell>
                    <span v-if="ticket['return_up_to'] === null">—</span>
                    <template v-else>
                        <div>Оформить возврат</div>
                        <div></div>
                    </template>
                </ListTableCell>
            </ListTableRow>
        </ListTable>

        <GuiMessage v-else-if="list.is_loaded">Ничего не найдено</GuiMessage>

        <GuiPagination :pagination="list.pagination" @pagination="(page, per_page) => list.load(page, per_page)"/>

    </LoadingProgress>
</template>

<script>
import list from "@/Core/List";
import LoadingProgress from "@/Components/LoadingProgress";
import LayoutFilters from "@/Components/Layout/LayoutFilters";
import LayoutFiltersItem from "@/Components/Layout/LayoutFiltersItem";
import BaseDateInput from "@/Components/Base/BaseDateInput";
import DictionaryDropDown from "@/Components/Inputs/DictionaryDropDown";
import InputSearch from "@/Components/Inputs/InputSearch";
import ListTable from "@/Components/ListTable/ListTable";
import ListTableRow from "@/Components/ListTable/ListTableRow";
import ListTableCell from "@/Components/ListTable/ListTableCell";
import GuiMessage from "@/Components/GUI/GuiMessage";
import GuiPagination from "@/Components/GUI/GuiPagination";

export default {
    props: {
        partnerId: {type: Number, default: null},
    },

    components: {
        GuiPagination,
        GuiMessage,
        ListTableCell,
        ListTableRow,
        ListTable,
        InputSearch,
        DictionaryDropDown,
        BaseDateInput,
        LayoutFiltersItem,
        LayoutFilters,
        LoadingProgress

    },

    data: () => ({
        list: null,
    }),

    created() {
        this.list = list('/api/registries/tickets', {partner_id: this.partnerId});
        this.list.initial();
    },

    methods: {
        highlight(text) {
            return this.$highlight(String(text), String(this.list.search), true);
        },
    }
}
</script>
