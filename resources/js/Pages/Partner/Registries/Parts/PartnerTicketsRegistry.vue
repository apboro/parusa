<template>
    <LoadingProgress :loading="list.is_loading">

        <LayoutFilters>
            <LayoutFiltersItem :title="'Период'" v-if="tripId === null">
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
            <LayoutFiltersItem :title="'Способ продажи'">
                <DictionaryDropDown
                    :dictionary="'partner_order_types'"
                    :fresh="true"
                    v-model="list.filters['order_type_id']"
                    :original="list.filters_original['order_type_id']"
                    :placeholder="'Все'"
                    :has-null="true"
                    :small="true"
                    :disabled="!!list.search"
                    @change="list.load()"
                />
            </LayoutFiltersItem>
            <template #search>
                <LayoutFiltersItem :title="'Поиск заказа/билета по номеру, имени, email, телефону покупателя'" style="max-width: 450px;">
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
            <ListTableRow v-for="ticket in list.list">
                <ListTableCell>
                    <div>{{ ticket['date'] }}</div>
                    <div>{{ ticket['time'] }}</div>
                </ListTableCell>
                <ListTableCell>
                    <div class="bold">
                        <router-link class="link" :to="{name: 'ticket-info', params: {id: ticket['id']}}" v-html="highlight(ticket['id'])"/>
                    </div>
                    <div>
                        <router-link class="link" :to="{name: 'order-info', params: {id: ticket['order_id']}}" v-html="highlight(ticket['order_id'])"/>
                    </div>
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

        <GuiMessage border v-else-if="list.is_loaded">Нет проданных билетов</GuiMessage>

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
import GuiMessage from "@/Components/GUI/GuiMessage";
import Pagination from "@/Components/Pagination";
import InputDate from "@/Components/Inputs/InputDate";
import GuiActionsMenu from "@/Components/GUI/GuiActionsMenu.vue";

export default {
    props: {
        partnerId: {type: Number, default: null},
        tripId: {type: Number, default: null},
        excursionId: {type: Number, default: null},
        pierId: {type: Number, default: null},
        shipId: {type: Number, default: null},
    },

    components: {
        GuiActionsMenu,
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
    }),

    created() {
        this.list = list('/api/registries/tickets', {
            partner_id: this.partnerId,
            trip_id: this.tripId,
            excursion_id: this.excursionId,
            pier_id: this.pierId,
            ship_id: this.shipId,
        });
        this.list.initial();
    },

    methods: {
        highlight(text) {
            return this.$highlight(String(text), String(this.list.search), true);
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
                        axios.post('/api/registries/tickets/export', options)
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
    }
}
</script>
