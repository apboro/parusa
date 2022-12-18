<template>
    <LayoutPage :title="$route.meta['title']">
        <LayoutFilters>
            <LayoutFiltersItem :title="'Период'">
                <InputDate
                    v-model="data.filters['date_from']"
                    :original="data.filters_original['date_from']"
                    @change="data.load()"
                />
                <InputDate
                    v-model="data.filters['date_to']"
                    :original="data.filters_original['date_to']"
                    @change="data.load()"
                />
            </LayoutFiltersItem>
        </LayoutFilters>
        <ListTable v-if="data.list && Object.keys(data.list).length > 0"
                   :titles="['Экскурсия', 'Продажа, руб.','Возвраты, руб.']">
            <ListTableRow v-for="row in data.list">
                <ListTableCell>
                    {{ row['name'] ? row['name'] : 'ИТОГО:'}}
                </ListTableCell>
                <ListTableCell>
                    {{ row['total_sold'] ? row['total_sold'] : row['total_plus']}} руб.
                </ListTableCell>
                <ListTableCell>
                    {{ row['total_returns'] ? row['total_returns'] + ' руб.' : row['total_minus'] ? row['total_minus'] + ' руб.' : 'нет'}}
                </ListTableCell>
            </ListTableRow>
        </ListTable>
        <GuiMessage border v-else-if="data.is_loaded">Ничего не найдено</GuiMessage>
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
        data: null,
        info: null,
    }),

    created() {
        this.data = list('/api/statistics/sales/list');
        this.data.initial();
    },

}
</script>
