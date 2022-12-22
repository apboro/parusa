<template>
    <LoadingProgress :loading="list.is_loading || fiscal_loading">

        <LayoutFilters>
            <LayoutFiltersItem :title="'Период'">
                <div class="w-210px mr-10">
                    <InputDateTime
                        v-model="list.filters['date_from']"
                        :original="list.filters_original['date_from']"
                        :disabled="!!list.search"
                        @change="list.load()"
                    />
                </div>
                <div class="w-210px">
                    <InputDateTime
                        v-model="list.filters['date_to']"
                        :original="list.filters_original['date_to']"
                        :from="list.filters['date_from']"
                        :disabled="!!list.search"
                        @change="list.load()"
                    />
                </div>
            </LayoutFiltersItem>
            <LayoutFiltersItem :title="'Касса'" v-if="terminalId === null">
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
                <div style="display: flex; align-items: flex-end; margin-left: 10px;">
                    <GuiActionsMenu :title="null">
                        <span class="link" @click="excelExport">Экспорт в Excel</span>
                    </GuiActionsMenu>
                </div>
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
                        <RouterLink v-if="transaction['order_id']" class="link"
                                    :to="{name: 'order-info', params: {id: transaction['order_id']}}"
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
                        <div v-if="transaction['by_card']"
                             v-html="'безнал.: ' + highlight(transaction['by_card'], true) + ' руб.'"></div>
                        <div v-if="transaction['by_cash']"
                             v-html="'нал.: ' + highlight(transaction['by_cash'], true) + ' руб.'"></div>
                    </ListTableCell>
                    <ListTableCell>
                        <div>
                            <RouterLink v-if="transaction['terminal_id']" class="link"
                                        :to="{name: 'terminals-view', params: {id: transaction['terminal_id']}}">
                                {{ transaction['terminal'] }}
                            </RouterLink>
                            <span v-else>—</span>
                        </div>
                        <div>
                            <RouterLink v-if="transaction['position_id']" class="link"
                                        :to="{name: 'staff-view', params: {id: transaction['position_id']}}">
                                {{ transaction['position'] }}
                            </RouterLink>
                            <span v-else>—</span>
                        </div>
                    </ListTableCell>
                    <ListTableCell>
                        <div>{{ transaction['gate'] }}: <span v-html="highlight(transaction['external_id'])"></span>
                        </div>
                        <div>ID заказа: <span v-html="highlight(transaction['order_external_id'])"></span></div>
                    </ListTableCell>
                </ListTableRow>
            </template>
        </ListTable>

        <GuiMessage border v-else-if="list.is_loaded">Ничего не найдено</GuiMessage>

        <Pagination :pagination="list.pagination" @pagination="(page, per_page) => list.load(page, per_page)"/>

        <GuiContainer mt-30>
            <GuiHeading text-lg>Статистика за период: <span class="bold">{{
                    list.payload['date_from']
                }} — {{ list.payload['date_to'] }}</span></GuiHeading>
            <GuiHeading text-md mt-20 bold v-if="list.payload['terminal']">Касса №{{
                    list.payload['terminal']
                }}
            </GuiHeading>
            <GuiHeading text-md mt-20 bold v-else>Касса: все</GuiHeading>
            <GuiContainer pr-40 pt-15 w-350px>
                <GuiValue :class="'w-300px'" :title="'приход общ.'">{{ list.payload['sale_total'] }} руб.</GuiValue>
                <GuiValue :class="'w-300px'" :title="'приход нал.'">{{ list.payload['sale_by_cash'] }} руб.</GuiValue>
                <GuiValue :class="'w-300px'" :title="'приход безнал.'">{{ list.payload['sale_by_card'] }} руб.
                </GuiValue>
            </GuiContainer>
            <GuiContainer pr-40 pt-15 w-350px>
                <GuiValue :class="'w-300px'" :title="'возврат нал.'">{{ list.payload['return_by_cash'] }} руб.
                </GuiValue>
                <GuiValue :class="'w-300px'" :title="'возврат безнал.'">{{ list.payload['return_by_card'] }} руб.
                </GuiValue>
            </GuiContainer>
        </GuiContainer>

        <PopUp ref="fiscal">
            <ScrollBox :mode="'vertical'">
                <GuiHint mx-20>
                    <div v-for="line in fiscal" v-html="line" style="font-family: monospace;"></div>
                </GuiHint>
            </ScrollBox>
        </PopUp>

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
import GuiMessage from "@/Components/GUI/GuiMessage";
import Pagination from "@/Components/Pagination";
import PopUp from "@/Components/PopUp";
import GuiHint from "@/Components/GUI/GuiHint";
import ScrollBox from "@/Components/ScrollBox";
import InputDateTime from "@/Components/Inputs/InputDateTime";
import InputDropDown from "@/Components/Inputs/InputDropDown";
import GuiContainer from "@/Components/GUI/GuiContainer";
import GuiHeading from "@/Components/GUI/GuiHeading";
import GuiValue from "@/Components/GUI/GuiValue";
import GuiActionsMenu from "@/Components/GUI/GuiActionsMenu.vue";

export default {
    props: {
        terminalId: {type: Number, default: null},
    },

    components: {
        GuiActionsMenu,
        GuiValue,
        GuiHeading,
        GuiContainer,
        InputDropDown,
        InputDateTime,
        ScrollBox,
        GuiHint,
        PopUp,
        LoadingProgress,
        LayoutFilters,
        LayoutFiltersItem,
        DictionaryDropDown,
        InputSearch,
        ListTable,
        ListTableRow,
        ListTableCell,
        GuiMessage,
        Pagination,
    },

    computed: {
        customFilters() {
            let filters = [
                {id: 'no-order', name: 'Не привязан к заказу'},
                {id: 'no-fiscal', name: 'Нет чека'},
                {id: 'no-cashier', name: 'Не определён кассир'},
            ];
            if (this.terminalId === null) {
                filters.push({id: 'no-terminal', name: 'Не привязан к кассе'});
            }
            return filters;
        }
    },

    data: () => ({
        list: list('/api/registries/transactions'),
        fiscal: null,
        fiscal_loading: false,
    }),

    created() {
        this.list.options = {terminal_id: this.terminalId};
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
                        axios.post('/api/registries/transactions/export', options)
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
