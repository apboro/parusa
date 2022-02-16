<template>
    <LayoutPage :loading="list.is_loading" :title="title">
        <template #actions>
            <GuiActionsMenu>
                <router-link class="link" :to="{ name: 'trip-edit', params: { id: 0 }, query: linkQuery}">Добавить рейс</router-link>
            </GuiActionsMenu>
        </template>

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

        <PopUp ref="status_popup" :title="popup_title"
               :buttons="[{result: 'no', caption: 'Отмена', color: 'white'}, {result: 'yes', caption: 'OK', color: 'green'}]"
               :manual="true"
        >
            <DictionaryDropDown :dictionary="popup_dictionary" v-model="current_status" :name="'status'" :original="initial_status"/>
        </PopUp>

        <TripDeletePopup ref="trip_delete"/>
    </LayoutPage>
</template>

<script>
import list from "@/Core/List";
import LayoutPage from "@/Components/Layout/LayoutPage";
import GuiActionsMenu from "@/Components/GUI/GuiActionsMenu";
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
import PopUp from "@/Components/PopUp";
import deleteEntry from "@/Mixins/DeleteEntry";
import TripDeletePopup from "@/Pages/Admin/Trips/Parts/TripDeletePopup";


export default {
    props: {
        excursionId: {type: Number, default: null},
        pierId: {type: Number, default: null},
    },

    components: {
        TripDeletePopup,
        PopUp,
        IconLink,
        IconExclamation,
        ListTableCell,
        ListTableRow,
        ListTable,
        GuiPagination,
        GuiMessage,
        IconForward,
        IconBackward,
        GuiIconButton,
        InputDate,
        DictionaryDropDown,
        LayoutFiltersItem,
        LayoutFilters,
        GuiActionsMenu,
        LayoutPage
    },

    mixins: [deleteEntry],

    data: () => ({
        list: list('/api/trips'),
        popup_title: null,
        popup_dictionary: null,
        initial_status: null,
        current_status: null,
    }),

    created() {
        this.list.initial();
    },

    computed: {
        linkQuery() {
            let query = {};
            if (this.list.filters['start_pier_id'] !== null) {
                query['pier'] = this.list.filters['start_pier_id'];
            }
            /**
             * For future use:
             * if (this.list.filters['excursion_id'] !== null) {
             *   query['excursion'] = this.list.filters['excursion_id'];
             * }
             */
            return query;
        },
        title() {
            return this.$route.meta['title']
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

        statusChange(trip) {
            this.popup_title = 'Статус движения';
            this.popup_dictionary = 'trip_statuses';
            this.initial_status = Number(trip['status_id']);
            this.current_status = this.initial_status;
            this.genericStatusChange('/api/trips/status', trip['id'])
                .then(data => {
                    this.list.list.some((item, key) => {
                        if (item['id'] === trip['id']) {
                            this.list.list[key]['status'] = data['status'];
                            this.list.list[key]['status_id'] = data['status_id'];
                            return true;
                        }
                        return false;
                    })
                });
        },
        saleStatusChange(trip) {
            this.popup_title = 'Статус продаж';
            this.popup_dictionary = 'trip_sale_statuses';
            this.initial_status = Number(trip['sale_status_id']);
            this.current_status = this.initial_status;
            this.genericStatusChange('/api/trips/sale-status', trip['id'])
                .then(data => {
                    this.list.list.some((item, key) => {
                        if (item['id'] === trip['id']) {
                            this.list.list[key]['sale_status'] = data['status'];
                            this.list.list[key]['sale_status_id'] = data['status_id'];
                            return true;
                        }
                        return false;
                    })
                });
        },
        genericStatusChange(url, id) {
            return new Promise((resolve, reject) => {
                this.$refs.status_popup.show()
                    .then(result => {
                        if (result === 'yes') {
                            this.$refs.status_popup.process(true);
                            axios.post(url, {id: id, status_id: this.current_status})
                                .then(response => {
                                    this.$toast.success(response.data.message, 3000);
                                    resolve(response.data.data);
                                })
                                .catch(error => {
                                    this.$toast.error(error.response.data.message);
                                    reject();
                                })
                                .finally(() => {
                                    this.$refs.status_popup.hide();
                                })
                        } else {
                            this.$refs.status_popup.hide();
                        }
                    });
            });
        },
    },
}
</script>
