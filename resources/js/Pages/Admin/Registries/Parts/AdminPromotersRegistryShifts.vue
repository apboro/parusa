<template>
    <LoadingProgress :loading="list.is_loading">
        <LayoutFilters>
            <LayoutFiltersItem :title="'Дата'">
                <InputDate
                    v-model="list.filters['date']"
                    :original="list.filters_original['date']"
                    :disabled="!!list.search"
                    @change="list.load()"
                />
            </LayoutFiltersItem>
            <LayoutFiltersItem :title="'Касса'">
                <DictionaryDropDown
                    :dictionary="'terminals'"
                    :fresh="true"
                    v-model="list.filters['terminal_id']"
                    :original="list.filters_original['terminal_id']"
                    :placeholder="'Все'"
                    :has-null="true"
                    :small="true"
                    @change="list.load()"
                />
            </LayoutFiltersItem>
            <template #search>
                <LayoutFiltersItem :title="'Поиск промоутера по ID, ФИО'">
                    <InputSearch v-model="list.search" @change="list.load()"/>
                </LayoutFiltersItem>
            </template>
        </LayoutFilters>

        <ListTable v-if="list.list && list.list.length > 0" :titles="list.titles">
            <template v-for="shift in list.list">
                <ListTableRow>
                    <ListTableCell>
                        {{ shift.id }}
                    </ListTableCell>
                    <ListTableCell>
                        {{ shift.name }}
                    </ListTableCell>
                    <ListTableCell>
                        {{ shift['start_at'] }}
                    </ListTableCell>
                    <ListTableCell>
                        {{ shift['end_at'] }}
                    </ListTableCell>
                    <ListTableCell>
                        {{ shift['pay_for_out'] > 0 ? shift['pay_for_out'] + ' руб.': null }}
                    </ListTableCell>
                    <ListTableCell>
                        {{ shift['working_hours'] > 0 ? shift['working_hours'] : null }}
                    </ListTableCell>
                    <ListTableCell>
                        {{ shift['tariff'] }}
                    </ListTableCell>
                    <ListTableCell>
                        {{ shift['pay_per_hour']  > 0 ? shift['pay_per_hour'] + ' руб.': null }}
                    </ListTableCell>
                    <ListTableCell>
                        {{ shift['pay_for_time']  > 0 ? shift['pay_for_time'] + ' руб.': null }}
                    </ListTableCell>
                    <ListTableCell>
                        {{ shift['sales_total'] > 0 ? shift['sales_total'] + ' руб.': null }}
                    </ListTableCell>
                    <ListTableCell>
                        {{ shift['pay_commission']  > 0 ? shift['pay_commission'] + ' руб.': null }}
                    </ListTableCell>
                    <ListTableCell>
                        {{ shift['pay_total']  > 0 ? shift['pay_total'] + ' руб.': null }}
                    </ListTableCell>
                    <ListTableCell>
                        {{ shift['paid_out']  > 0 ? shift['paid_out'] + ' руб.': null }}
                    </ListTableCell>
                </ListTableRow>
            </template>
        </ListTable>
        <GuiMessage border v-else-if="list.is_loaded">Ничего не найдено</GuiMessage>
        <GuiValue v-if="list.payload.total_to_pay_out" :title="'Всего начислено:'"> {{ list.payload.total_to_pay_out }} руб.</GuiValue>
        <GuiValue v-if="list.payload.total_paid_out" :title="'Всего выплачено:'"> {{ list.payload.total_paid_out }} руб.</GuiValue>

        <Pagination :pagination="list.pagination" @pagination="(page, per_page) => list.load(page, per_page)"/>
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
import IconExclamation from "../../../../Components/Icons/IconExclamation";
import InputPhone from "@/Components/Inputs/InputPhone.vue";

export default {
    props: {
    },

    components: {
        InputPhone,
        IconExclamation,
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
        this.list = list('/api/registries/promoters/shifts', {});
        this.list.initial();
    },

    methods: {
        highlight(text) {
            return this.$highlight(String(text), String(this.list.search), true);
        },

        highlightPartial(text) {
            return this.$highlight(String(text), String(this.list.search));
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
