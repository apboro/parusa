<template>
    <LoadingProgress :loading="list.is_loading">
        <LayoutFilters>
            <LayoutFiltersItem :title="'Период продажи'">
                <div class="w-210px mr-10">
                    <InputDateTime
                        v-model="list.filters['date_from']"
                        :original="list.filters_original['date_from']"
                        @change="list.load()"
                        :disabled="!!list.search"
                    />
                </div>
                <div class="w-210px">
                    <InputDateTime
                        v-model="list.filters['date_to']"
                        :original="list.filters_original['date_to']"
                        @change="list.load()"
                        :disabled="!!list.search"
                    />
                </div>
                <GuiButton style="margin-left: 10px" @clicked="resetDate" :disabled="!!list.search">Сегодня</GuiButton>
            </LayoutFiltersItem>
            <LayoutFiltersItem :title="'Дата рейса'">
                <div class="w-150px">
                    <InputDate
                        v-model="list.filters['trip_date']"
                        :original="list.filters_original['trip_date']"
                        @change="list.load()"
                        :clearable="true"
                        :pick-on-clear="false"
                        :disabled="!!list.search"
                    />
                </div>
            </LayoutFiltersItem>
            <LayoutFiltersItem :title="'Партнер'">
                <FieldDropDown
                    :options="partners"
                    v-model="list.filters['partner_id']"
                    :identifier="'id'"
                    :show="'name'"
                    :original="list.filters_original['partner_id']"
                    :placeholder="'Все'"
                    :has-null="true"
                    :small="true"
                    :hide-title="true"
                    :disabled="!!list.search"
                    @change="list.load()"
                />
            </LayoutFiltersItem>
            <LayoutFiltersItem :title="'Поиск'"
                               style="max-width: 100%;">
                <InputSearch v-model="list.search" @change="list.load()"/>

                <div style="display: flex; align-items: flex-end; margin-left: 10px">
                    <GuiActionsMenu :title="null">
                        <span class="link" @click="excelExport">Экспорт в Excel</span>
                    </GuiActionsMenu>
                </div>
            </LayoutFiltersItem>
        </LayoutFilters>
        <LayoutFilters>
            <LayoutFiltersItem :title="'Экскурсии'" style="margin-left: 10px; width: 100%;">
                <div class="w-450px">
                    <DictionaryDropDown
                        :dictionary="'excursions'"
                        :fresh="true"
                        v-model="list.filters['excursion_ids']"
                        :original="list.filters_original['excursion_ids']"
                        :placeholder="'Все'"
                        :has-null="true"
                        :search="true"
                        :small="true"
                        :disabled="!!list.search"
                        :multi="true"
                        @change="list.load()"
                    />
                </div>
            </LayoutFiltersItem>
        </LayoutFilters>
        <ListTable v-if="list.list && list.list.length > 0" :titles="list.titles">
            <ListTableRow v-for="ticket in list.list">
                <ListTableCell>
                    <div>{{ ticket['date'] }}</div>
                    <div>{{ ticket['time'] }}</div>
                </ListTableCell>
                <ListTableCell>
                    <div class="bold">
                        <router-link class="link" :to="{name: 'ticket-info', params: {id: ticket['id']}}"
                                     v-html="highlight(ticket['id'])"/>
                    </div>
                    <div>
                        <router-link class="link" :to="{name: 'order-info', params: {id: ticket['order_id']}}"
                                     v-html="highlight(ticket['order_id'])"/>
                    </div>
                </ListTableCell>
                <ListTableCell>
                    <div>{{ ticket['type'] }}</div>
                    <div>{{ ticket['amount'] }} руб.</div>
                </ListTableCell>
                <ListTableCell>
                    <div><b>№{{ ticket['trip_id'] }}</b> {{ ticket['trip_date'] }} {{ ticket['trip_time'] }}</div>
                    <div>{{ ticket['excursion'] }} {{ ticket['pier'] }}</div>
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

        <GuiMessage v-else-if="list.is_loaded" border>Нет проданных билетов</GuiMessage>

        <Pagination :pagination="list.pagination" @pagination="(page, per_page) => list.load(page, per_page)"/>

        <GuiContainer v-if="totals" mt-30>
            <GuiHeading text-lg>Статистика за период: <span class="bold">{{
                    list.payload['dateFrom']
                }} — {{ list.payload['dateTo'] }}</span><span> по партнёру: {{list.payload.partner}}</span>
            </GuiHeading>
            <GuiValue :title="'Всего билетов:'">{{totals.ticketsCount}}</GuiValue>
            <GuiValue :title="'Сумма билетов:'">{{totals.totalSum}} руб.</GuiValue>
        </GuiContainer>
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
import InputDate from "@/Components/Inputs/InputDate";
import InputDateTime from "@/Components/Inputs/InputDateTime";
import GuiButton from "@/Components/GUI/GuiButton";
import GuiActionsMenu from "@/Components/GUI/GuiActionsMenu.vue";
import FieldDropDown from "@/Components/Fields/FieldDropDown.vue";
import GuiContainer from "@/Components/GUI/GuiContainer.vue";
import GuiValue from "@/Components/GUI/GuiValue.vue";
import GuiHeading from "@/Components/GUI/GuiHeading.vue";

export default {
    props: {},

    components: {
        GuiHeading,
        GuiValue,
        GuiContainer,
        FieldDropDown,
        GuiActionsMenu,
        GuiButton,
        InputDateTime,
        InputDate,
        Pagination,
        GuiMessage,
        ListTableCell,
        ListTableRow,
        ListTable,
        InputSearch,
        DictionaryDropDown,
        LayoutFiltersItem,
        LayoutFilters,
        LoadingProgress

    },

    data: () => ({
        list: null,
        partnerId: null,
    }),

    created() {
        this.list = list('/api/registries/filling');
        this.list.initial();
    },
    computed: {
        partners() {
            return this.list.payload.partners;
        },
        totals(){
            return this.list.payload.totals;
        }
    },
    methods: {
        highlight(text) {
            return this.$highlight(String(text), String(this.list.search), true);
        },
        resetDate() {
            this.list.filters['date_from'] = null;
            this.list.filters['date_to'] = null;
            this.list.load();
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
                        this.list.is_loading = true;
                        let options = {
                            filters: this.list.filters,
                            search: this.list.search,
                            partner_id: this.partnerId,
                        }
                        axios.post('/api/registries/filling/export', options)
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
                                this.list.is_loading = false;
                            });
                    }
                });
        },
    }
}
</script>
