<script>
import DictionaryDropDown from "@/Components/Inputs/DictionaryDropDown.vue";
import InputDate from "@/Components/Inputs/InputDate.vue";
import GuiValue from "@/Components/GUI/GuiValue.vue";
import GuiHint from "@/Components/GUI/GuiHint.vue";
import ListTableCell from "@/Components/ListTable/ListTableCell.vue";
import ListTableRow from "@/Components/ListTable/ListTableRow.vue";
import Pagination from "@/Components/Pagination.vue";
import GuiMessage from "@/Components/GUI/GuiMessage.vue";
import ListTable from "@/Components/ListTable/ListTable.vue";
import LayoutFiltersItem from "@/Components/Layout/LayoutFiltersItem.vue";
import LayoutFilters from "@/Components/Layout/LayoutFilters.vue";
import GuiHeading from "@/Components/GUI/GuiHeading.vue";
import GuiContainer from "@/Components/GUI/GuiContainer.vue";
import LoadingProgress from "@/Components/LoadingProgress.vue";
import list from "@/Core/List";

export default {
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

    props: {
       partnerId: Number
    },

    data: () => ({
        list: null,
    }),

    created() {
        this.list = list('/api/promoters/pay_outs', {id: this.partnerId});
        this.list.initial();
    },

}
</script>

<template>
    <LoadingProgress :loading="this.list.is_loading">

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
        </LayoutFilters>

        <GuiContainer w-100>
            <ListTable v-if="list.list.length > 0" :titles="list.titles">
                <ListTableRow v-for="payout in list.list">

                    <ListTableCell :nowrap="true">
                        {{payout.start_at}}
                    </ListTableCell>

                    <ListTableCell>
                        {{payout.paid_out}} руб.
                    </ListTableCell>

                </ListTableRow>
            </ListTable>
            <GuiMessage v-else-if="list.is_loaded">Нет операций за выбранный период</GuiMessage>
            <Pagination :pagination="list.pagination" @pagination="(page, per_page) => list.load(page, per_page)"/>
        </GuiContainer>

        <GuiContainer mt-30>
            <GuiValue :class="'w-300px'" :title="'Сумма по выборке'">{{ list.payload['selected_total'] }} руб.
            </GuiValue>
            <GuiValue :class="'w-300px'" :title="'Сумма на странице'">{{ list.payload['selected_page_total'] }} руб.
            </GuiValue>
        </GuiContainer>
    </LoadingProgress>
</template>

<style scoped lang="scss">

</style>
