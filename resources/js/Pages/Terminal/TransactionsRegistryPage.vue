<template>
    <LayoutPage :title="$route.meta['title']">
        <LayoutFilters>
            <LayoutFiltersItem :title="'Период'">
                <div class="w-210px mr-10">
                    <InputDateTime
                        v-model="list.filters['date_from']"
                        :original="list.filters_original['date_from']"
                        @change="list.load()"
                    />
                </div>
                <div class="w-210px">
                    <InputDateTime
                        v-model="list.filters['date_to']"
                        :original="list.filters_original['date_to']"
                        :from="list.filters['date_from']"
                        @change="list.load()"
                    />
                </div>
            </LayoutFiltersItem>
            <LayoutFiltersItem :title="'Выборка'">
                <InputDropDown
                    v-model="list.filters['select']"
                    :original="list.filters_original['select']"
                    :options="customFilters"
                    :identifier="'id'"
                    :show="'name'"
                    :placeholder="'Все'"
                    :has-null="true"
                    :small="true"
                    @change="list.load()"
                />
            </LayoutFiltersItem>
            <template #search>
                <LayoutFiltersItem :title="'Поиск по номеру заказа, транзакции, сумме'">
                    <InputSearch v-model="list.search" @change="list.load()"/>
                </LayoutFiltersItem>
            </template>
        </LayoutFilters>

        <ListTable v-if="list.list && list.list.length > 0" :titles="list.titles">
            <template v-for="transaction in list.list">
                <ListTableRow>
                    <ListTableCell>
                        <div>{{ transaction['date'] }}</div>
                        <div>{{ transaction['time'] }}</div>
                    </ListTableCell>
                    <ListTableCell>
                        <RouterLink v-if="transaction['order_id']" class="link" :to="{name: 'order-info', params: {id: transaction['order_id']}}"
                                    v-html="highlight(transaction['order_id'], true)"/>
                        <span v-else>—</span>
                    </ListTableCell>
                    <ListTableCell>
                        <div>{{ transaction['status'] }}</div>
                        <div v-if="transaction['fiscal']" class="link" @click="showFiscal(transaction)">чек</div>
                        <div v-else>нет чека</div>
                    </ListTableCell>
                    <ListTableCell>
                        <div><b v-html="highlight(transaction['total'], true) + ' руб.'"></b></div>
                        <div v-if="transaction['by_card']" v-html="'безнал.: ' + highlight(transaction['by_card'], true) + ' руб.'"></div>
                        <div v-if="transaction['by_cash']" v-html="'нал.: ' + highlight(transaction['by_cash'], true) + ' руб.'"></div>
                    </ListTableCell>
                    <ListTableCell>
                        <div>
                            <span v-if="transaction['terminal_id']">{{ transaction['terminal'] }}</span>
                            <span v-else>—</span>
                        </div>
                        <div>
                            <span v-if="transaction['position_id']">{{ transaction['position'] }}</span>
                            <span v-else>—</span>
                        </div>
                    </ListTableCell>
                    <ListTableCell>
                        <div>{{ transaction['gate'] }}: <span v-html="highlight(transaction['external_id'])"></span></div>
                        <div>ID заказа: <span v-html="highlight(transaction['order_external_id'])"></span></div>
                    </ListTableCell>
                </ListTableRow>
            </template>
        </ListTable>

        <GuiMessage border v-else-if="list.is_loaded">Ничего не найдено</GuiMessage>

        <Pagination :pagination="list.pagination" @pagination="(page, per_page) => list.load(page, per_page)"/>

        <GuiContainer mt-30>
            <GuiHeading text-lg>Статистика за период <span class="bold">{{ list.payload['date_from'] }} - {{ list.payload['date_to'] }}</span></GuiHeading>
            <GuiHeading text-md mt-20 bold v-if="list.payload['terminal']">Касса №{{ list.payload['terminal'] }}</GuiHeading>
            <GuiContainer pr-40 pt-15 w-350px>
                <GuiHeading text-md mt-15 mb-10 bold>Наличными</GuiHeading>
                <GuiValue :class="'w-300px'" :title="'приход'">{{ list.payload['sale_by_cash'] }} руб.</GuiValue>
                <GuiValue :class="'w-300px'" :title="'возврат'">{{ list.payload['return_by_cash'] }} руб.</GuiValue>
                <GuiValue :class="'w-300px'" :title="'итого'">{{ list.payload['cash_total'] }} руб.</GuiValue>
            </GuiContainer>
            <GuiContainer pr-40 pt-15 w-350px>
                <GuiHeading text-md mt-15 mb-10 bold>Безналичными</GuiHeading>
                <GuiValue :class="'w-300px'" :title="'приход'">{{ list.payload['sale_by_card'] }} руб.</GuiValue>
                <GuiValue :class="'w-300px'" :title="'возврат'">{{ list.payload['return_by_card'] }} руб.</GuiValue>
                <GuiValue :class="'w-300px'" :title="'итого'">{{ list.payload['card_total'] }} руб.</GuiValue>
            </GuiContainer>
            <GuiContainer pr-40 pt-15 w-350px>
                <GuiHeading text-md mt-15 mb-10 bold>Общий</GuiHeading>
                <GuiValue :class="'w-300px'" :title="'приход'">{{ list.payload['sale_total'] }} руб.</GuiValue>
                <GuiValue :class="'w-300px'" :title="'возврат'">{{ list.payload['return_total'] }} руб.</GuiValue>
                <GuiValue :class="'w-300px'" :title="'итого'">{{ list.payload['overall_total'] }} руб.</GuiValue>
            </GuiContainer>
        </GuiContainer>

        <PopUp ref="fiscal">
            <ScrollBox :mode="'vertical'">
                <GuiHint mx-20>
                    <div v-for="line in fiscal" v-html="line" style="font-family: monospace;"></div>
                </GuiHint>
            </ScrollBox>
        </PopUp>
    </LayoutPage>
</template>

<script>
import LayoutPage from "@/Components/Layout/LayoutPage";
import list from "@/Core/List";
import LayoutFilters from "@/Components/Layout/LayoutFilters";
import LayoutFiltersItem from "@/Components/Layout/LayoutFiltersItem";
import InputDateTime from "@/Components/Inputs/InputDateTime";
import InputDropDown from "@/Components/Inputs/InputDropDown";
import InputSearch from "@/Components/Inputs/InputSearch";
import ListTable from "@/Components/ListTable/ListTable";
import ListTableRow from "@/Components/ListTable/ListTableRow";
import ListTableCell from "@/Components/ListTable/ListTableCell";
import GuiMessage from "@/Components/GUI/GuiMessage";
import Pagination from "@/Components/Pagination";
import GuiContainer from "@/Components/GUI/GuiContainer";
import GuiHeading from "@/Components/GUI/GuiHeading";
import GuiValue from "@/Components/GUI/GuiValue";
import PopUp from "@/Components/PopUp";
import ScrollBox from "@/Components/ScrollBox";
import GuiHint from "@/Components/GUI/GuiHint";

export default {
    components: {
        GuiHint,
        ScrollBox,
        PopUp,
        GuiValue,
        GuiHeading,
        GuiContainer,
        Pagination,
        GuiMessage,
        ListTableCell,
        ListTableRow,
        ListTable,
        InputSearch,
        InputDropDown,
        InputDateTime,
        LayoutFiltersItem,
        LayoutFilters,
        LayoutPage,
    },

    computed: {
        customFilters() {
            return [
                {id: 'no-order', name: 'Не привязан к заказу'},
                {id: 'no-fiscal', name: 'Нет чека'},
                {id: 'no-cashier', name: 'Не определён кассир'},
            ];
        }
    },

    data: () => ({
        list: list('/api/registries/transactions'),
        fiscal: null,
        fiscal_loading: false,
    }),

    created() {
        this.list.initial();
    },

    methods: {
        showFiscal(transaction) {
            this.fiscal_loading = true;
            axios.post('/api/registries/transactions/fiscal', {gate: transaction['gate'], id: transaction['fiscal']})
                .then(response => {
                    let fiscal = response.data.data['fiscal'];
                    this.fiscal = String(fiscal).replaceAll(' ', '&nbsp;').split("\n");
                    this.$refs.fiscal.show(true);
                })
                .catch(error => {
                    this.$refs.fiscal.hide();
                    this.$toast.error(error.response.data['message']);
                })
                .finally(() => {
                    this.fiscal_loading = false;
                });
        },

        highlight(text, full = false) {
            return this.$highlight(String(text), String(this.list.search), full);
        },
    },
}
</script>
