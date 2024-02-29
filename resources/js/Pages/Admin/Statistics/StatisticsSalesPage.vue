<template>
    <LayoutPage :title="$route.meta['title']" :loading="list.is_loading">
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
        </LayoutFilters>
        <ListTable v-if="list.list && Object.keys(list.list).length > 0" :titles="['Экскурсия', 'Продажа, руб.','Возвраты, руб.']">
            <ListTableRow v-for="row in list.list">
                <ListTableCell>
                    {{ row['name'] }}
                </ListTableCell>
                <ListTableCell>
                    {{ row['sold_amount'] }} руб.
                </ListTableCell>
                <ListTableCell>
                    {{ row['returned_amount'] }} руб.
                </ListTableCell>
            </ListTableRow>
            <ListTableRow>
                <ListTableCell>
                    Итого
                </ListTableCell>
                <ListTableCell>
                    {{ list.payload['sold_amount_total'] }} руб.
                </ListTableCell>
                <ListTableCell>
                    {{ list.payload['return_amount_total'] }} руб.
                </ListTableCell>
            </ListTableRow>
        </ListTable>
        <GuiMessage border v-else-if="list.is_loaded">Ничего не найдено</GuiMessage>
    </LayoutPage>
</template>

<script>
import LayoutPage from "@/Components/Layout/LayoutPage";
import LayoutFilters from "@/Components/Layout/LayoutFilters.vue";
import LayoutFiltersItem from "@/Components/Layout/LayoutFiltersItem.vue";
import InputDate from "@/Components/Inputs/InputDate.vue";
import GuiMessage from "@/Components/GUI/GuiMessage.vue";
import Pagination from "@/Components/Pagination.vue";
import list from "@/Core/List";
import ListTable from "@/Components/ListTable/ListTable.vue";
import ListTableRow from "@/Components/ListTable/ListTableRow.vue";
import ListTableCell from "@/Components/ListTable/ListTableCell.vue";


export default {
    components: {
        ListTableCell,
        ListTableRow,
        ListTable,
        LayoutPage,
        LayoutFilters,
        LayoutFiltersItem,
        InputDate,
        GuiMessage,
        Pagination,
    },

    data: () => ({
        list: null,
        info: null,
    }),

    created() {
        this.list = list('/api/statistics/sales/list');
        this.list.initial();
    },

}
</script>
