<template>
    <LoadingProgress :loading="list.is_loading">
        <LayoutFilters>
            <LayoutFiltersItem :title="'Период'">
                <InputDate
                    v-model="list.filters['date_from']"
                    :original="list.filters_original['date_from']"
                    :disabled="!!list.search"
                    @change="list.load()"
                />
                <InputDate
                    v-model="list.filters['date_to']"
                    :original="list.filters_original['date_to']"
                    :disabled="!!list.search"
                    @change="list.load()"
                />
            </LayoutFiltersItem>
            <template #search>
                <LayoutFiltersItem :title="'Поиск промоутера по ID, ФИО'">
                    <InputSearch v-model="list.search" @change="list.load()"/>
                </LayoutFiltersItem>
            </template>
        </LayoutFilters>



    </LoadingProgress>
</template>

<script>
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

    },

    methods: {
        expandTickets(order) {
            order['show_tickets'] = !order['show_tickets'];
        },

        highlight(text) {
            return this.$highlight(String(text), String(this.list.search), true);
        },

        highlightPartial(text) {
            return this.$highlight(String(text), String(this.list.search));
        },

        highlightPhone(text) {
            return this.$highlight(String(text), String(this.list.filters['search_phone']));
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
