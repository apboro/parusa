<template>
    <LoadingProgress :loading="list.is_loading">

        <LayoutFilters v-if="tripId === null">
            <LayoutFiltersItem :title="'Период продажи'">
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
                        @change="list.load()"
                    />
                </div>
                <GuiButton style="margin-left: 10px" @clicked="resetDate">Сегодня</GuiButton>
            </LayoutFiltersItem>
            <template #search>
                <LayoutFiltersItem :title="'Дата рейса'">
                    <div class="w-150px">
                        <InputDate
                            v-model="list.filters['trip_date']"
                            :original="list.filters_original['trip_date']"
                            @change="list.load()"
                            :clearable="true"
                            :pick-on-clear="false"
                        />
                    </div>
                </LayoutFiltersItem>
                <LayoutFiltersItem :title="'Экскурсия'" style="margin-left: 10px; width: 280px;">
                    <DictionaryDropDown
                        :dictionary="'excursions'"
                        :fresh="true"
                        v-model="list.filters['excursion_id']"
                        :original="list.filters_original['excursion_id']"
                        :placeholder="'Все'"
                        :has-null="true"
                        :search="true"
                        :small="true"
                        @change="list.load()"
                    />
                </LayoutFiltersItem>
            </template>
        </LayoutFilters>

        <LayoutFilters style="padding-top: 0;">
            <LayoutFiltersItem :title="'Способ продажи'">
                <DictionaryDropDown
                    :dictionary="'order_types'"
                    :fresh="true"
                    v-model="list.filters['order_type_id']"
                    :original="list.filters_original['order_type_id']"
                    :placeholder="'Все'"
                    :has-null="true"
                    :small="true"
                    @change="saleMethodChanged"
                />
            </LayoutFiltersItem>
            <LayoutFiltersItem :title="'Касса'" v-if="is_terminal_filter">
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
                <LayoutFiltersItem :title="'Поиск заказа/билета по номеру, имени, email, телефону покупателя'" style="max-width: 450px;">
                    <InputSearch v-model="list.search" @change="list.load()"/>
                </LayoutFiltersItem>
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
                    <div style="white-space: normal;">{{ ticket['order_type'] }}</div>
                    <div v-if="ticket['terminal']">{{ ticket['terminal'] }}</div>
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

        <GuiMessage v-else-if="list.is_loaded" border>Нет проданных билетов</GuiMessage>

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
import InputDateTime from "@/Components/Inputs/InputDateTime";
import GuiButton from "@/Components/GUI/GuiButton";

export default {
    props: {
        partnerId: {type: Number, default: null},
        tripId: {type: Number, default: null},
        excursionId: {type: Number, default: null},
        pierId: {type: Number, default: null},
        shipId: {type: Number, default: null},
    },

    components: {
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
        is_terminal_filter: false,
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
        resetDate() {
            this.list.filters['date_from'] = null;
            this.list.filters['date_to'] = null;
            this.list.load();
        },
        saleMethodChanged(value) {
            this.is_terminal_filter = value === 3 || value === 4;
            this.list.filters['terminal_id'] = null;
            this.list.load();
        },
    }
}
</script>
