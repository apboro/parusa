<template>
    <LoadingProgress :loading="list.is_loading">
        <LayoutFilters>
            <LayoutFiltersItem :class="'w-25'" :title="'Дата'">
                <GuiIconButton :class="'mr-5'" :border="false" @click="setDay(-1)">
                    <IconBackward/>
                </GuiIconButton>
                <InputDate
                    :original="list.filters_original['date']"
                    v-model="list.filters['date']"
                    :pick-on-clear="false"
                    :small="true"
                    @change="dateChanged"
                    ref="date"
                />
                <GuiIconButton :class="'ml-5'" :border="false" @click="setDay(1)">
                    <IconForward/>
                </GuiIconButton>
            </LayoutFiltersItem>
            <LayoutFiltersItem :class="'w-25'" :title="'Статус движения'">
                <DictionaryDropDown
                    :dictionary="'trip_statuses'"
                    :fresh="true"
                    v-model="list.filters['status_id']"
                    :original="list.filters_original['status_id']"
                    :placeholder="'Все'"
                    :has-null="true"
                    :small="true"
                    @change="list.load()"
                />
            </LayoutFiltersItem>
            <LayoutFiltersItem :class="'w-25'" :title="'Экскурсия'" v-if="excursionId === null">
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
            <LayoutFiltersItem :class="'w-25'" :title="'Причал отправления'" v-if="pierId === null">
                <DictionaryDropDown
                    :dictionary="'piers'"
                    :fresh="true"
                    v-model="list.filters['start_pier_id']"
                    :original="list.filters_original['start_pier_id']"
                    :placeholder="'Все'"
                    :has-null="true"
                    :search="true"
                    :right="true"
                    :small="true"
                    @change="list.load()"
                />
            </LayoutFiltersItem>
        </LayoutFilters>

        <ListTable v-if="list.list.length > 0" :titles="list.titles" :has-action="true">
            <ListTableRow v-for="trip in list.list">
                <ListTableCell>
                    <div><b>
                        <router-link :class="'link'" :to="{name: 'trip-view', params: {id: trip['id']}}">{{ trip['start_time'] }}</router-link>
                    </b></div>
                    <div>{{ trip['start_date'] }}</div>
                </ListTableCell>
                <ListTableCell>
                    <router-link :class="'link'" :to="{name: 'trip-view', params: {id: trip['id']}}">{{ trip['id'] }}</router-link>
                </ListTableCell>
                <ListTableCell>
                    {{ trip['excursion'] }}
                </ListTableCell>
                <ListTableCell>
                    <div>{{ trip['pier'] }}</div>
                    <div>{{ trip['ship'] }}</div>
                </ListTableCell>
                <ListTableCell>{{ trip['tickets_total'] - trip['tickets_count'] }} ({{ trip['tickets_total'] }})</ListTableCell>
                <ListTableCell>
                    <div>
                        <span class="link" @click="statusChange(trip)">{{ trip['status'] }}</span>
                    </div>
                    <div>
                        <span class="link" v-if="trip['has_rate']" @click="saleStatusChange(trip)">{{ trip['sale_status'] }}</span>
                        <span class="text-red" v-else><IconExclamation :class="'h-1em inline'"/> Тариф не задан</span>
                    </div>
                </ListTableCell>
                <ListTableCell :nowrap="true" :class="'flex justify-end'">
                    <GuiIconButton v-if="trip['chained']" :class="'mr-5'" :color="'blue'" @click="chainInfo(trip)">
                        <IconLink/>
                    </GuiIconButton>
                    <GuiActionsMenu :title="null">
                        <router-link :to="{name: 'trip-edit', params: {id: trip['id']}}" class="link">Редактировать</router-link>
                        <router-link :to="{name: 'trip-edit', params: {id: 0}, query: {from: trip['id']}}" class="link">Копировать рейс</router-link>
                        <span class="link" @click="remove(trip)">Удалить</span>
                    </GuiActionsMenu>
                </ListTableCell>
            </ListTableRow>
        </ListTable>

        <GuiMessage border v-else-if="list.is_loaded">Ничего не найдено</GuiMessage>

        <GuiPagination :pagination="list.pagination" @pagination="(page, per_page) => list.load(page, per_page)"/>

        <FormPopUp :title="form_title"
                   :form="form"
                   :options="{id: trip_id}"
                   ref="popup"
        >
            <GuiContainer w-350px>
                <FormDictionary v-if="dictionary !== null" :form="form" :name="'value'" :dictionary="dictionary" :fresh="true" :hide-title="true"/>
            </GuiContainer>
        </FormPopUp>

        <TripDeletePopup ref="trip_delete"/>
    </LoadingProgress>
</template>

<script>
import list from "@/Core/List";
import deleteEntry from "@/Mixins/DeleteEntry";
import TripDeletePopup from "@/Pages/Admin/Trips/Parts/TripDeletePopup";

import GuiActionsMenu from "@/Components/GUI/GuiActionsMenu";
import LoadingProgress from "@/Components/LoadingProgress";
import LayoutFilters from "@/Components/Layout/LayoutFilters";
import LayoutFiltersItem from "@/Components/Layout/LayoutFiltersItem";
import DictionaryDropDown from "@/Components/Inputs/DictionaryDropDown";
import InputDate from "@/Components/Inputs/InputDate";
import GuiIconButton from "@/Components/GUI/GuiIconButton";
import IconBackward from "@/Components/Icons/IconBackward";
import IconForward from "@/Components/Icons/IconForward";
import GuiMessage from "@/Components/GUI/GuiMessage";
import GuiPagination from "@/Components/GUI/GuiPagination";
import ListTable from "@/Components/ListTable/ListTable";
import ListTableRow from "@/Components/ListTable/ListTableRow";
import ListTableCell from "@/Components/ListTable/ListTableCell";
import IconExclamation from "@/Components/Icons/IconExclamation";
import IconLink from "@/Components/Icons/IconLink";
import FormPopUp from "@/Components/FormPopUp";
import GuiContainer from "@/Components/GUI/GuiContainer";
import FormDictionary from "@/Components/Form/FormDictionary";
import form from "@/Core/Form";

export default {
    props: {
        excursionId: {type: Number, default: null},
        pierId: {type: Number, default: null},
    },

    emits: ['setTitle', 'setStartPier'],

    components: {
        FormDictionary,
        GuiContainer,
        FormPopUp,
        LoadingProgress,
        LayoutFilters, LayoutFiltersItem,
        DictionaryDropDown,
        InputDate,
        GuiIconButton, IconForward, IconBackward,
        IconLink,
        ListTableCell, ListTableRow, ListTable,
        GuiActionsMenu,
        IconExclamation,
        GuiMessage,
        GuiPagination,
        TripDeletePopup,
    },

    mixins: [deleteEntry],

    data: () => ({
        list: list('/api/trips'),
        form: form('', '/api/trips/properties'),
        form_title: null,
        dictionary: null,
        trip_id: null,
    }),

    created() {
        this.list.loaded_callback = () => {
            this.$emit('setTitle', this.title);
            this.$emit('setStartPier', this.list.filters['start_pier_id']);
        }
        this.list.initial();
    },

    computed: {
        title() {
            return 'Список рейсов'
                + (this.list.payload['date'] ? ' на ' + this.list.payload['date'] : '')
                + (!isNaN(this.list.payload['day']) ? ', ' + ['воскресенье', 'понедельник', 'вторник', 'среда', 'четверг', 'пятница', 'суббота'][this.list.payload['day']] : '');
        },
    },

    methods: {
        dateChanged(name, value) {
            if (value !== null) {
                this.list.load();
            }
        },
        setDay(increment) {
            this.$refs.date.addDays(increment);
        },
        chainInfo(trip) {
            let count = trip['chain_trips_count'];
            let date_from = trip['chain_trips_start_at'];
            let date_to = trip['chain_trips_end_at'];
            let message = 'Рейс имеет связанные рейсы с аналогичными параметрами в диапазоне: <b>' + date_from + ' - ' + date_to + '</b>';
            message += '<br/>';
            message += '<br/>';
            message += 'Количество связанных рейсов: <b>' + count + '</b>';
            this.$dialog.show(message, 'info', 'green', [this.$dialog.button('ok', 'ok', 'green')], 'center');
        },
        remove(trip) {
            if (!trip['chained']) {
                this.deleteEntry('Удалить рейс №' + trip['id'] + '?', '/api/trips/delete', {id: trip['id'], mode: 'single'})
                    .then(() => {
                        this.list.reload();
                    });
            } else {
                this.$refs.trip_delete.remove(trip)
                    .then(() => {
                        this.list.reload();
                    })
            }
        },
        showForm(trip, title, key, rules, dictionary = null) {
            this.trip_id = trip['id'];
            this.form_title = title;
            this.form.reset();
            this.form.set('name', key);
            this.form.set('value', trip[key], rules, title, true);
            this.dictionary = dictionary;
            this.form.toaster = this.$toast;
            this.form.load();
            this.$refs.popup.show()
                .then(response => {
                    this.list.list.some((item, index) => {
                        if (item['id'] === trip['id']) {
                            Object.keys(response.payload).map(key => {
                                this.list.list[index][key] = response.payload[key];
                            })
                            return true;
                        }
                        return false;
                    })
                })
        },
        statusChange(trip) {
            this.showForm(trip, 'Статус движения', 'status_id', 'required', 'trip_statuses');
        },
        saleStatusChange(trip) {
            this.showForm(trip, 'Статус продаж', 'sale_status_id', 'required', 'trip_sale_statuses');
        },
    },
}
</script>
