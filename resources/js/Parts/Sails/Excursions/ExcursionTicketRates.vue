<template>
    <loading-progress :loading="processing">
        <hint mx-10 my-15>Указанные тарифы будут применены по умолчанию ко всем продаваемым билетам. Исключение составляют билеты, реализуемые партнёрами по специальным
            условиям.
        </hint>
        <div class="text-right mb-20" v-if="editable">
            <span class="link" @click="createNewRate">Добавить тариф</span>
        </div>

        <heading bold>Действующий тариф</heading>
        <message text-red border mt-15 v-if="currentRate === null">Действующий тариф не задан</message>
        <excursion-ticket-rates-rate v-else
                                     :rate="currentRate"
                                     :today="today"
                                     :editable="editable"
                                     @edit="editRate"
                                     @createFrom="createNewRateFrom"
                                     @delete="deleteRate"
        />
        <hint mx-10 mb-15 mt-25>
            1. Параметр применяется только на мобильных кассах.
        </hint>
        <hint mx-10 my-15>
            2. Указанное комиссионное вознаграждение назначается партнёрам вне зависимости от способа продаж. Если для партнёра не прописаны специальные условия, расчёт
            вознаграждения ведётся по этой колонке.
        </hint>

        <heading bold mt-30>Будущие тарифы</heading>
        <message text-red border mt-15 v-if="comingRates.length === 0">Будущих тарифов нет</message>
        <excursion-ticket-rates-rate v-else v-for="rate in comingRates"
                                     :rate="rate"
                                     :today="today"
                                     :editable=editable
                                     @edit="editRate"
                                     @createFrom="createNewRateFrom"
                                     @delete="deleteRate"
        />

        <heading bold mt-30>Прошлые тарифы</heading>
        <message border mt-15 v-if="archivedRates.length === 0">Прошлых тарифов нет</message>
        <excursion-ticket-rates-rate v-else v-for="rate in archivedRates"
                                     :rate="rate"
                                     :today="today"
                                     :editable=editable
                                     @edit="editRate"
                                     @createFrom="createNewRateFrom"
                                     @delete="deleteRate"
        />

        <pop-up ref="popup" v-if="editable" :title="popup_title"
                :buttons="[{result: 'no', caption: 'Отмена', color: 'white'}, {result: 'yes', caption: 'OK', color: 'green'}]"
                :resolving="formResolving"
                :manual="true"
        >
            <excursion-ticket-rates-form :form="form"/>
        </pop-up>
    </loading-progress>
</template>

<script>
import genericDataSource from "@/Helpers/Core/genericDataSource";
import {parseRules} from "@/Helpers/Core/validator/validator";
import formDataSource from "@/Helpers/Core/formDataSource";
import moment from "moment";
import DeleteEntry from "@/Mixins/DeleteEntry";

import LoadingProgress from "@/Components/LoadingProgress";
import Container from "@/Components/GUI/Container";
import Hint from "@/Components/GUI/Hint";
import Heading from "@/Components/GUI/Heading";
import Message from "@/Components/GUI/Message";
import PopUp from "@/Components/PopUp";
import ExcursionTicketRatesRate from "@/Parts/Sails/Excursions/ExcursionTicketRatesRate";
import ExcursionTicketRatesForm from "./ExcursionTicketRatesForm";

export default {
    props: {
        excursionId: {type: Number, required: true},
        editable: {type: Boolean, default: false},
    },

    mixins: [DeleteEntry],

    components: {
        ExcursionTicketRatesRate,
        Message,
        Heading,
        ExcursionTicketRatesForm,
        PopUp,
        LoadingProgress,
        Hint,
        Container,
    },

    data: () => ({
        ticket_grades_loaded: false,
        data: null,
        popup_title: null,
        form: null,
    }),

    computed: {
        processing() {
            return !this.ticket_grades_loaded || this.data.loading || this.deleting;
        },

        currentRate() {
            let current = null;
            this.data.data.some(rate => {
                if (moment(rate['start_at'], 'DD.MM.YYYY') <= this.today && this.today <= moment(rate['end_at'], 'DD.MM.YYYY')) {
                    current = rate;
                    return true;
                }
                return false;
            });
            return current;
        },

        comingRates() {
            let rates = [];
            this.data.data.map(rate => {
                if (moment(rate['start_at'], 'DD.MM.YYYY') > this.today) {
                    rates.push(rate);
                }
            });
            return rates.sort((a, b) => moment(a['start_at'], 'DD.MM.YYYY') - moment(b['start_at'], 'DD.MM.YYYY'));
        },

        archivedRates() {
            let rates = [];
            this.data.data.map(rate => {
                if (moment(rate['end_at'], 'DD.MM.YYYY') < this.today) {
                    rates.push(rate);
                }
            });
            return rates.sort((a, b) => moment(b['start_at'], 'DD.MM.YYYY') - moment(a['start_at'], 'DD.MM.YYYY'));
        },

        today() {
            return moment(this.data.payload['today'], 'DD.MM.YYYY');
        },
    },

    created() {
        this.refreshTicketGrades();
        this.data = genericDataSource('/api/excursions/rates');
        this.data.load({id: this.excursionId});
    },

    methods: {
        refreshTicketGrades() {
            if (!this.ticket_grades_loaded) {
                this.$store.dispatch('dictionary/refresh', 'ticket_grades')
                    .then(() => {
                        this.ticket_grades_loaded = true;
                    });
            }
        },

        editRate(rate) {
            this.makeTicketRateForm(rate['id'], rate);
        },

        createNewRateFrom(rate) {
            this.makeTicketRateForm(0, rate);
        },

        createNewRate() {
            this.makeTicketRateForm(0, null);
        },

        deleteRate(rate) {
            const message = 'Удаление тарифа приведет к остановке продаж билетов на рейсы в диапазоне дат ' + rate['start_at'] + ' - ' + rate['end_at'] + '. Продолжить?';
            const id = rate['id'];

            this.deleteEntry(message, '/api/excursions/rates/delete', {id: id})
                .then(() => {
                    this.data.data = this.data.data.filter(rate => rate['id'] !== id);
                });
        },

        formResolving(result) {
            if (result !== 'yes') {
                return true;
            } else if (!this.form.validateAll()) {
                return false;
            }

            this.$refs.popup.process(true);
            this.form.save();
            return false;
        },

        rateSaved(payload) {
            let replaced = this.data.data.some((rate, key) => {
                if (rate['id'] === payload.rate['id']) {
                    this.data.data[key] = payload.rate;
                    return true;
                }
                return false;
            });
            if (!replaced) {
                this.data.data.push(payload.rate);
            }
            this.$refs.popup.hide();
        },

        makeTicketRateForm(rateId, data) {
            this.$store.dispatch('dictionary/refresh', 'ticket_grades')
                .then(() => {
                    this.form = formDataSource(null, '/api/excursions/rates/update', {excursionId: this.excursionId, id: rateId});
                    this.form.toaster = this.$toast;
                    this.form.afterSave = this.rateSaved;
                    this.form.failedSave = () => {
                        this.$refs.popup.process(false);
                    };

                    this.popup_title = rateId === 0 ? 'Добавление тарифа' : 'Изменение тарифа';

                    this.form.values = {start_at: data === null ? null : data['start_at'], end_at: data === null ? null : data['end_at']};
                    this.form.originals = {start_at: data === null ? null : data['start_at'], end_at: data === null ? null : data['end_at']};
                    this.form.titles = {start_at: 'Начало действия тарифа', end_at: 'Окончание действия тарифа'};
                    this.form.validation_rules = {
                        start_at: parseRules('required|date|after_or_equal:' + this.data.payload['today'] + '|bail'),
                        end_at: parseRules('required|date|after:start_at|bail'),
                        zero: {},
                    };

                    // render helper
                    this.form.payload['count'] = [];
                    let index = 0;

                    this.$store.getters['dictionary/dictionary']('ticket_grades').map(item => {
                        let dataRate = null;
                        if (data === null && item['enabled'] || data !== null && data['rates'].some(rate => (rate['grade_id'] === item['id']) && (dataRate = rate))) {
                            this.form.values['rates.' + index + '.rate_id'] = rateId;
                            this.form.values['rates.' + index + '.grade_id'] = item['id'];
                            this.form.values['rates.' + index + '.grade_name'] = item['name'];
                            this.form.values['rates.' + index + '.base_price'] = dataRate === null ? null : dataRate['base_price'];
                            this.form.values['rates.' + index + '.min_price'] = dataRate === null ? null : dataRate['min_price'];
                            this.form.values['rates.' + index + '.max_price'] = dataRate === null ? null : dataRate['max_price'];
                            this.form.values['rates.' + index + '.commission_type'] = dataRate === null ? null : dataRate['commission_type'];
                            this.form.values['rates.' + index + '.commission_value'] = dataRate === null ? null : dataRate['commission_value'];
                            this.form.originals['rates.' + index + '.rate_id'] = rateId;
                            this.form.originals['rates.' + index + '.grade_id'] = item['id'];
                            this.form.originals['rates.' + index + '.base_price'] = dataRate === null ? null : dataRate['base_price'];
                            this.form.originals['rates.' + index + '.min_price'] = dataRate === null ? null : dataRate['min_price'];
                            this.form.originals['rates.' + index + '.max_price'] = dataRate === null ? null : dataRate['max_price'];
                            this.form.originals['rates.' + index + '.commission_type'] = dataRate === null ? null : dataRate['commission_type'];
                            this.form.originals['rates.' + index + '.commission_value'] = dataRate === null ? null : dataRate['commission_value'];
                            this.form.titles['rates.' + index + '.base_price'] = 'Базовая цена';
                            this.form.titles['rates.' + index + '.min_price'] = 'Минимальная';
                            this.form.titles['rates.' + index + '.max_price'] = 'Максимальная';
                            this.form.titles['rates.' + index + '.commission_type'] = 'Тип';
                            this.form.titles['rates.' + index + '.commission_value'] = 'Комиссия';
                            this.form.validation_rules['rates.' + index + '.rate_id'] = {};
                            this.form.validation_rules['rates.' + index + '.grade_name'] = {};
                            this.form.validation_rules['rates.' + index + '.grade_id'] = {};
                            this.form.validation_rules['rates.' + index + '.base_price'] = parseRules('required|integer|min:0|bail');
                            this.form.validation_rules['rates.' + index + '.min_price'] = parseRules('required|integer|min:0|bail');
                            this.form.validation_rules['rates.' + index + '.max_price'] = parseRules('required|integer|gte:rates.' + index + '.base_price|min:0|bail');
                            this.form.validation_rules['rates.' + index + '.commission_type'] = parseRules('required');
                            this.form.validation_rules['rates.' + index + '.commission_value'] = parseRules('required|integer|min:0|bail');
                            this.form.payload['count'].push(index);
                            index++;
                        }
                    });
                    this.form.loaded = true;
                    this.$refs.popup.show()
                        .then(() => {
                            this.$refs.popup.hide();
                        });
                });
        },
    }
}
</script>
