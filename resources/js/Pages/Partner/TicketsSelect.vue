<template>
    <list-page :loading="list.is_loading">

        <template v-slot:header>
            <page-title-bar :title="$route.meta['title']"/>
        </template>

        <template v-slot:filters>
            <page-bar-item :class="'w-25'" :title="'Дата'">
                <GuiIconButton :class="'mr-5'" :border="false" @click="setDay(-1)">
                    <IconBackward/>
                </GuiIconButton>
                <base-date-input v-model="list.filters['date']"
                                 :original="list.filters_original['date']"
                />
                <GuiIconButton :class="'ml-5'" :border="false" @click="setDay(1)">
                    <IconForward/>
                </GuiIconButton>
            </page-bar-item>
            <page-bar-item :class="'w-25'" :title="'Тип программы'">
                <dictionary-drop-down v-model="list.filters['program_id']"
                                      :dictionary="'excursion_programs'"
                                      :placeholder="'Все'"
                                      :has-null="true"
                                      :original="list.filters_original['program_id']"
                />
            </page-bar-item>
            <page-bar-item :class="'w-25'" :title="'Экскурсия'">
                <dictionary-drop-down v-model="list.filters['excursion_id']"
                                      :dictionary="'excursions'"
                                      :placeholder="'Все'"
                                      :has-null="true"
                                      :search="true"
                                      :original="list.filters_original['excursion_id']"
                />
            </page-bar-item>
            <page-bar-item :class="'w-25'">
                <GuiButton @click="list.load()">Подобрать</GuiButton>
            </page-bar-item>
        </template>

        <base-table v-if="!empty(list.list)">
            <template v-slot:header>
                <base-table-head :header="list.titles" :has-actions="true"/>
            </template>
            <base-table-row v-for="(trip, key) in list.list" :key="key">
                <base-table-cell>
                    <BaseTableCellItem><b class="text-lg">{{ trip['start_time'] }}</b></BaseTableCellItem>
                    <BaseTableCellItem>
                        <GuiHint>{{ trip['start_date'] }}</GuiHint>
                    </BaseTableCellItem>
                </base-table-cell>
                <base-table-cell>
                    <BaseTableCellItem><b>{{ trip['excursion'] }}</b></BaseTableCellItem>
                    <BaseTableCellItem>
                        <GuiHint>{{ !empty(trip['programs']) ? trip['programs'].join(', ') : '' }}</GuiHint>
                    </BaseTableCellItem>
                </base-table-cell>
                <base-table-cell>{{ trip['pier'] }}</base-table-cell>
                <base-table-cell>
                    {{ trip['tickets_total'] - trip['tickets_count'] }} ({{ trip['tickets_total'] }})
                </base-table-cell>
                <base-table-cell>
                    <table class="rates-table text-sm" v-if="trip['rates'] && trip['rates'].length > 0" style="white-space: nowrap">
                        <tr v-for="rate in trip['rates']">
                            <td class="pr-15">{{ rate['name'] }}</td>
                            <td>{{ rate['value'] }} руб.</td>
                        </tr>
                    </table>
                </base-table-cell>
                <base-table-cell>
                    <GuiButton @click="addToOrder(trip)">Выбрать</GuiButton>
                </base-table-cell>
            </base-table-row>
        </base-table>

        <GuiMessage v-else-if="list.loaded">Ничего не найдено</GuiMessage>

        <GuiPagination :pagination="list.pagination" @pagination="(page, per_page) => list.load(page, per_page)"/>

        <PopUp :title="'Добавить в заказ'" ref="orderPopup"
               :resolving="formResolving"
               :buttons="[{result: 'no', caption: 'Отмена', color: 'white'}, {result: 'yes', caption: 'OK', color: 'green'}]"
        >
            <TripTicketsAddForm :form="form" ref="orderForm"/>
        </PopUp>
    </list-page>
</template>

<script>
import ListPage from "@/Layouts/ListPage";
import PageTitleBar from "@/Layouts/Parts/PageTitleBar";
import PageBarItem from "@/Layouts/Parts/PageBarItem";
import GuiIconButton from "@/Components/GUI/GuiIconButton";
import IconBackward from "@/Components/Icons/IconBackward";
import BaseDateInput from "@/Components/Base/BaseDateInput";
import IconForward from "@/Components/Icons/IconForward";
import DictionaryDropDown from "@/Components/Dictionary/DictionaryDropDown";
import GuiButton from "@/Components/GUI/GuiButton";
import GuiPagination from "@/Components/GUI/GuiPagination";
import GuiMessage from "@/Components/GUI/GuiMessage";
import list from "@/Core/List";
import empty from "@/Mixins/empty";
import moment from "moment";
import GuiHint from "@/Components/GUI/GuiHint";
import UseBaseTableBundle from "@/Mixins/UseBaseTableBundle";
import PopUp from "@/Components/PopUp";
import TripTicketsAddForm from "@/Parts/Sails/Trips/TripTicketsAddForm";
import formDataSource from "@/Helpers/Core/formDataSource";

export default {
    components: {
        TripTicketsAddForm,
        PopUp,
        GuiHint,
        GuiMessage,
        GuiPagination,
        GuiButton,
        DictionaryDropDown,
        IconForward,
        BaseDateInput,
        IconBackward,
        GuiIconButton,
        PageBarItem,
        PageTitleBar,
        ListPage,
    },

    mixins: [empty, UseBaseTableBundle],

    data: () => ({
        list: null,
        form: null,
    }),

    created() {
        this.list = list('/api/trips/select');
        this.list.initial();
        this.form = formDataSource(null, '/api/trips/add_tickets_to_cart');
        this.form.toaster = this.$toast;
        this.form.afterSave = () => {
            this.$refs.orderPopup.hide();
            this.$store.dispatch('partner/refresh');
        };
        this.form.failedSave = () => {
            this.$refs.orderPopup.process(false);
        };
    },

    methods: {
        setDay(increment) {
            let date = moment(this.list.filters['date'], 'DD.MM.YYYY');
            this.list.filters['date'] = date.date(date.date() + increment).format('DD.MM.YYYY');
            this.list.load()
        },

        addToOrder(trip) {
            this.form.values = {};
            this.form.originals = {};
            this.form.validation_rules = {};
            this.form.validation_errors = {};
            this.form.payload = {count: []};

            let index = 0;
            this.form.setField('trip_id', trip['id'], null);
            trip['rates'].map(grade => {
                this.form.setField('tickets.' + index + '.grade_id', grade['id'], null);
                this.form.setField('tickets.' + index + '.grade_name', grade['name'], null);
                this.form.setField('tickets.' + index + '.base_price', Number(grade['value']), 'nullable|integer|min:0');
                this.form.setField('tickets.' + index + '.quantity', 0, 'integer|min:0', 'Количество');
                this.form.payload['count'].push(index);
                index++;
            })

            this.form.loaded = true;
            this.$refs.orderPopup.show();
        },

        formResolving(result) {
            if (result !== 'yes') {
                return true;
            } else if (!this.form.validateAll()) {
                return false;
            }

            this.$refs.orderPopup.process(true);
            this.form.save();
            return false;
        },
    }
}
</script>
