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
                    <div><b>{{ trip['start_time'] }}</b></div>
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
                <ListTableCell :nowrap="true" :class="'flex'">
                    <GuiIconButton v-if="trip['chained']" :class="'mr-5'" :color="'blue'" @click="chainInfo(trip)">
                        <IconLink/>
                    </GuiIconButton>
                    <GuiActionsMenu :title="null">
                        <span class="link">Редактировать</span>
                        <span class="link">Копировать рейс</span>
                        <span class="link">Удалить</span>
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
    </LayoutPage>
<!--
    <list-page :loading="list.loading">

        <template v-slot:header>
            <page-title-bar :title="title">
                <actions-menu>
                    <router-link class="link" :to="{ name: 'trip-edit', params: { id: 0 }, query: linkQuery}">Добавить рейс</router-link>
                </actions-menu>
            </page-title-bar>
        </template>

        <template v-slot:filters>
            <page-bar-item :class="'w-25'" :title="'Дата'">
                <ButtonIcon :class="'mr-5'" :border="false" @click="setDay(-1)">
                    <IconBackward/>
                </ButtonIcon>
                <InputDate
                    :original="list.filters_original['date']"
                    v-model="list.filters['date']"
                    :pick-on-clear="false"
                    :small="true"
                    @change="dateChanged"
                />
                <ButtonIcon :class="'ml-5'" :border="false" @click="setDay(1)">
                    <IconForward/>
                </ButtonIcon>
            </page-bar-item>
            <page-bar-item :class="'w-25'" :title="'Статус движения'">
                <DictionaryDropDown
                    :dictionary="'trip_statuses'"
                    v-model="list.filters['status_id']"
                    :original="list.filters_original['status_id']"
                    :placeholder="'Все'"
                    :has-null="true"
                    :small="true"
                    @change="reload"
                />
            </page-bar-item>
            <page-bar-item :class="'w-25'" :title="'Экскурсия'">
                <DictionaryDropDown
                    :dictionary="'excursions'"
                    v-model="list.filters['excursion_id']"
                    :original="list.filters_original['excursion_id']"
                    :placeholder="'Все'"
                    :has-null="true"
                    :search="true"
                    :small="true"
                    @change="reload"
                />
            </page-bar-item>
            <page-bar-item :class="'w-25'" :title="'Причал отправления'">
                <DictionaryDropDown
                    :dictionary="'piers'"
                    v-model="list.filters['start_pier_id']"
                    :original="list.filters_original['start_pier_id']"
                    :placeholder="'Все'"
                    :has-null="true"
                    :search="true"
                    :right="true"
                    :small="true"
                    @change="reload"
                />
            </page-bar-item>
        </template>

        <base-table v-if="!empty(list.data)">
            <template v-slot:header>
                <base-table-head :header="list.titles" :has-actions="true"/>
            </template>
            <base-table-row v-for="(trip, key) in list.data" :key="key">
                <base-table-cell>
                    <base-table-cell-item><b>{{ trip['start_time'] }}</b></base-table-cell-item>
                    <base-table-cell-item>{{ trip['start_date'] }}</base-table-cell-item>
                </base-table-cell>
                <base-table-cell>
                    <router-link :class="'link'" :to="{name: 'trip-view', params: {id: trip['id']}}">{{ trip['id'] }}</router-link>
                </base-table-cell>
                <base-table-cell>
                    {{ trip['excursion'] }}
                </base-table-cell>
                <base-table-cell>
                    <base-table-cell-item>{{ trip['pier'] }}</base-table-cell-item>
                    <base-table-cell-item>{{ trip['ship'] }}</base-table-cell-item>
                </base-table-cell>
                <base-table-cell>{{ trip['tickets_total'] - trip['tickets_count'] }} ({{ trip['tickets_total'] }})</base-table-cell>
                <base-table-cell>
                    <base-table-cell-item>
                        <span class="link" @click="statusChange(trip)">{{ trip['status'] }}</span>
                    </base-table-cell-item>
                    <base-table-cell-item>
                        <span class="link" v-if="trip['has_rate']" @click="saleStatusChange(trip)">{{ trip['sale_status'] }}</span>
                        <span class="text-red" v-else><IconExclamation :class="'h-1em inline'"/> Тариф не задан</span>
                    </base-table-cell-item>
                </base-table-cell>
                <base-table-cell :nowrap="true" :class="'right'">
                    <button-icon v-if="trip['chained']" :class="'mr-5'" :color="'blue'" @click="chainInfo(trip)">
                        <icon-link/>
                    </button-icon>
                    <actions-menu :title="null">
                        <span class="link">Редактировать</span>
                        <span class="link">Копировать рейс</span>
                        <span class="link">Удалить</span>
                    </actions-menu>
                </base-table-cell>
            </base-table-row>
        </base-table>
        <message v-else-if="list.loaded">Ничего не найдено</message>

        <base-pagination :pagination="list.pagination" @pagination="setPagination"/>

        <pop-up ref="status_popup" :title="popup_title"
                :buttons="[{result: 'no', caption: 'Отмена', color: 'white'}, {result: 'yes', caption: 'OK', color: 'green'}]"
                :manual="true"
        >
            <dictionary-drop-down :dictionary="popup_dictionary" v-model="current_status" :name="'status'" :original="initial_status"/>
        </pop-up>
    </list-page>
    -->
</template>

<script>
import moment from "moment";
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


export default {
    props: {
        excursionId: {type: Number, default: null},
        pierId: {type: Number, default: null},
    },

    components: {
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
            return this.$route.meta['title'] + (this.list.filters['date'] ? ' на ' + this.list.filters['date'] : '');
        },
    },

    methods: {
        dateChanged(name, value) {
            if (value !== null) {
                this.list.load();
            }
        },
        setDay(increment) {
            let date = moment(this.list.filters['date'], 'DD.MM.YYYY');
            this.list.filters['date'] = date.date(date.date() + increment).format('DD.MM.YYYY');
            this.list.load();
        },
        chainInfo(trip) {
            axios.post('/api/trips/info', {id: trip['id']})
                .then(response => {
                    let date_from = response.data.data['date_from'];
                    let date_to = response.data.data['date_to'];
                    let count = response.data.data['count'];
                    let message = 'Рейс имеет связанные рейсы с аналогичными параметрами в диапазоне: <b>' + date_from + ' - ' + date_to + '</b>';
                    message += '<br/>';
                    message += '<br/>';
                    message += 'Количество связанных рейсов: <b>' + count + '</b>';
                    this.$dialog.show(message, 'info', 'green', [this.$dialog.button('ok', 'ok', 'green')], 'center');
                })
                .catch(error => {
                    this.$toast.error(error.response.data.message, 5000);
                })
        },

        statusChange(trip) {
            this.popup_title = 'Статус движения';
            this.popup_dictionary = 'trip_statuses';
            this.initial_status = Number(trip['status_id']);
            this.current_status = this.initial_status;
            this.genericStatusChange('/api/trips/status', trip['id'])
                .then(data => {
                    this.list.data.some((item, key) => {
                        if (item['id'] === trip['id']) {
                            this.list.data[key]['status'] = data['status'];
                            this.list.data[key]['status_id'] = data['status_id'];
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
                    this.list.data.some((item, key) => {
                        if (item['id'] === trip['id']) {
                            this.list.data[key]['sale_status'] = data['status'];
                            this.list.data[key]['sale_status_id'] = data['status_id'];
                            return true;
                        }
                        return false;
                    })
                    // this.datasource.data['sale_status'] = data['status'];
                    // this.datasource.data['sale_status_id'] = data['status_id'];
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
        }
    },
}
</script>
