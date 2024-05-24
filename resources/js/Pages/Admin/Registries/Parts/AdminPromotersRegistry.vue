<template>
    <LoadingProgress :loading="list.is_loading">
        <LayoutFilters>
            <LayoutFiltersItem :title="'Период'">
                <InputDateTime
                    v-model="list.filters['date_from']"
                    :original="list.filters_original['date_from']"
                    @change="list.load()"
                />
                <InputDateTime
                    v-model="list.filters['date_to']"
                    :original="list.filters_original['date_to']"
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
                <div style="display: flex; align-items: flex-end; margin-left: 10px;">
                    <GuiActionsMenu :title="null">
                        <span class="link" @click="excelExport">Экспорт в Excel</span>
                    </GuiActionsMenu>
                </div>
            </template>
        </LayoutFilters>

        <ListTable v-if="list.list && list.list.length > 0" :titles="list.titles">
            <template v-for="promoter in list.list">
                <ListTableRow>
                    <ListTableCell>
                        {{promoter.id}}
                    </ListTableCell>
                    <ListTableCell>
                        {{promoter.name}}
                    </ListTableCell>
                    <ListTableCell>
                        {{promoter['total_hours']  > 0 ? promoter['total_hours'] : '—' }}
                    </ListTableCell>
                    <ListTableCell>
                        {{promoter['sales_total']  > 0 ? promoter['sales_total'] + ' руб.': '—' }}
                    </ListTableCell>
                    <ListTableCell>
                        {{promoter['commission_scarlet_sails']  > 0 ? promoter['commission_scarlet_sails'] + ' руб.': '—' }}
                    </ListTableCell>
                    <ListTableCell>
                        {{promoter['commission_partners']  > 0 ? promoter['commission_partners'] + ' руб.': '—' }}
                    </ListTableCell>
                    <ListTableCell>
                        {{promoter['taxi']  > 0 ? promoter['taxi'] + ' руб.': '—' }}
                    </ListTableCell>
                    <ListTableCell>
                        {{promoter['total_to_pay_out']  > 0 ? promoter['total_to_pay_out'] + ' руб.': '—' }}
                    </ListTableCell>
                    <ListTableCell>
                        {{promoter['total_paid_out']  > 0 ? promoter['total_paid_out'] + ' руб.': '—' }}
                    </ListTableCell>
                    <ListTableCell>
                        {{promoter['balance']  > 0 ? promoter['balance'] + ' руб.': '—' }}
                    </ListTableCell>

                </ListTableRow>
            </template>
        </ListTable>
        <GuiMessage border v-else-if="list.is_loaded">Ничего не найдено</GuiMessage>
        <GuiValue v-if="list.payload.total_to_pay_out" :title="'Всего начислено:'"> {{ list.payload.total_to_pay_out }} руб.</GuiValue>
        <GuiValue v-if="list.payload.total_paid_out" :title="'Всего выплачено:'"> {{ list.payload.total_paid_out }} руб.</GuiValue>
        <GuiValue v-if="list.payload.total_earned" :title="'Кассы итого:'"> {{ list.payload.total_earned }} руб.</GuiValue>

        <Pagination :pagination="list.pagination" @pagination="(page, per_page) => list.load(page, per_page)"/>
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
import list from "@/Core/List";
import GuiActionsMenu from "@/Components/GUI/GuiActionsMenu.vue";
import InputDateTime from "@/Components/Inputs/InputDateTime.vue";

export default {
    props: {

    },

    components: {
        InputDateTime,
        GuiActionsMenu,
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
        this.list = list('/api/registries/promoters', {});
        this.list.initial();
    },

    methods: {

        highlight(text) {
            return this.$highlight(String(text), String(this.list.search), true);
        },

        highlightPartial(text) {
            return this.$highlight(String(text), String(this.list.search));
        },
        excelExport() {
            this.$dialog.show('Экспортировать ' + this.list.pagination.total + ' записей в Excel?',
                null,
                'blue',
                [
                    this.$dialog.button('yes', 'Экспортировать', 'blue'),
                    this.$dialog.button('no', 'Отмена', 'default'),
                ]
            )
                .then(result => {
                    if (result === 'yes') {
                        this.is_exporting = true;
                        let options = {
                            filters: this.list.filters,
                            search: this.list.search,
                        }
                        axios.post('/api/registries/promoters/export', options)
                            .then(response => {
                                let file = atob(response.data.data['file']);
                                let byteNumbers = new Array(file.length);
                                for (let i = 0; i < file.length; i++) {
                                    byteNumbers[i] = file.charCodeAt(i);
                                }
                                let byteArray = new Uint8Array(byteNumbers);
                                let blob = new Blob([byteArray], {type: response.data.data['type']});

                                saveAs(blob, response.data.data['file_name'], {autoBom: true});
                            })
                            .catch(error => {
                                this.$toast.error(error.response.data['message']);
                            })
                            .finally(() => {
                                this.is_exporting = false;
                            });
                    }
                });
        },

    },
}
</script>

<style lang="scss" scoped>
$project_font: -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica Neue, Arial, Noto Sans, sans-serif, Apple Color Emoji, Segoe UI Emoji, Segoe UI Symbol, Noto Color Emoji !default;
</style>
