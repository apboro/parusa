<template>
    <loading-progress :loading="processing">
        <hint mx-10 my-15>Тарифы и комисионное вознаграждение партнёра за продажу услуг компании "Алые Паруса"</hint>
        <hint mx-10 mb-15 mt-25>1. Параметр применяется только на мобильных кассах.</hint>
        <hint mx-10 my-15>
            2. Указанное комиссионное вознаграждение назначается партнёрам вне зависимости от способа продаж. Если для партнёра не прописаны специальные условия, расчёт
            вознаграждения ведётся по этой колонке.
        </hint>

        <template v-for="excursion in data.data">
            <heading bold mt-30>{{ excursion['name'] }}</heading>
            <message v-if="excursion['rates'].length === 0">Тарифы не заданы</message>
            <partner-ticket-rates-rate v-else v-for="rate in excursion['rates']"
                                       :rate="rate"
                                       :excursion="{id: excursion['id'], name: excursion['name']}"
                                       :today="today"
                                       :editable="editable"
                                       @edit="editRate"
            />
        </template>

        <pop-up ref="popup" v-if="editable" :title="'Установить специальные условия для партнёра'"
                :buttons="[{result: 'no', caption: 'Отмена', color: 'white'}, {result: 'yes', caption: 'OK', color: 'green'}]"
                :resolving="formResolving"
                :manual="true"
        >
            <partner-ticket-rates-form :form="form"/>
        </pop-up>
    </loading-progress>
</template>

<script>
import genericDataSource from "../../../Helpers/Core/genericDataSource";
import {parseRules} from "@/Helpers/Core/validator/validator";
import formDataSource from "../../../Helpers/Core/formDataSource";
import LoadingProgress from "../../../Components/LoadingProgress";
import Hint from "../../../Components/GUI/Hint";
import Heading from "../../../Components/GUI/Heading";
import PopUp from "../../../Components/PopUp";
import moment from "moment";
import Message from "../../../Layouts/Parts/Message";
import PartnerTicketRatesRate from "./PartnerTicketRatesRate";
import PartnerTicketRatesForm from "./PartnerTicketRatesForm";

export default {
    props: {
        partnerId: {type: Number, required: true},
        editable: {type: Boolean, default: false},
    },

    components: {
        PartnerTicketRatesForm,
        PartnerTicketRatesRate,
        Message,
        Heading,
        PopUp,
        LoadingProgress,
        Hint,
    },

    data: () => ({
        ticket_grades_loaded: false,
        data: null,
        form: null,
    }),

    computed: {
        processing() {
            return !this.ticket_grades_loaded || this.data.loading;
        },

        today() {
            return moment(this.data.payload['today'], 'DD.MM.YYYY');
        },
    },

    created() {
        this.refreshTicketGrades();
        this.data = genericDataSource('/api/partners/rates');
        this.data.load({id: this.partnerId});
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

        editRate(rate, excursion) {
            this.$store.dispatch('dictionary/refresh', 'ticket_grades')
                .then(() => {
                    this.form = formDataSource(null, '/api/partners/rates/update', {
                        partnerId: this.partnerId, excursionId: excursion['id'], rateId: rate['id']
                    });
                    this.form.toaster = this.$toast;
                    this.form.afterSave = this.rateSaved;
                    this.form.failedSave = () => {
                        this.$refs.popup.process(false);
                    };

                    this.form.values = {start_at: rate['start_at'], end_at: rate['end_at'], title: excursion['name']};
                    this.form.validation_rules = {start_at: {}, end_at: {}, title: {}};

                    // render helper
                    this.form.payload['count'] = [];
                    let index = 0;

                    this.$store.getters['dictionary/dictionary']('ticket_grades').map(item => {
                        let dataRate = null;
                        if (rate['rates'].some(rate => (rate['grade_id'] === item['id']) && (dataRate = rate))) {
                            this.form.values['rates.' + index + '.id'] = dataRate['id'];
                            this.form.values['rates.' + index + '.grade_id'] = item['id'];
                            this.form.values['rates.' + index + '.grade_name'] = item['name'];
                            this.form.values['rates.' + index + '.base_price'] = dataRate['base_price'];
                            this.form.values['rates.' + index + '.commission_type'] = dataRate['commission_type'];
                            this.form.values['rates.' + index + '.commission_value'] = dataRate['commission_value'];
                            this.form.validation_rules['rates.' + index + '.id'] = {};
                            this.form.validation_rules['rates.' + index + '.grade_id'] = {};
                            this.form.validation_rules['rates.' + index + '.grade_name'] = {};
                            this.form.validation_rules['rates.' + index + '.base_price'] = {};
                            this.form.validation_rules['rates.' + index + '.commission_type'] = {};
                            this.form.validation_rules['rates.' + index + '.commission_value'] = {};

                            this.form.values['rates.' + index + '.partner_commission_type'] = dataRate['partner_commission_type'];
                            this.form.values['rates.' + index + '.partner_commission_value'] = dataRate['partner_commission_value'];
                            this.form.originals['rates.' + index + '.partner_commission_type'] = dataRate['partner_commission_type'];
                            this.form.originals['rates.' + index + '.partner_commission_value'] = dataRate['partner_commission_value'];
                            this.form.titles['rates.' + index + '.partner_commission_type'] = 'Тип';
                            this.form.titles['rates.' + index + '.partner_commission_value'] = 'Комиссия';
                            this.form.validation_rules['rates.' + index + '.partner_commission_type'] = {};
                            this.form.validation_rules['rates.' + index + '.partner_commission_value'] = parseRules('nullable|integer|min:0|bail');

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

        // deleteRate(rate, excursion) {
            //     const message = 'Удаление тарифа приведет к остановке продаж билетов на рейсы в диапазоне дат ' + rate['start_at'] + ' - ' + rate['end_at'] + '. Продолжить?';
            //     const id = rate['id'];
            //
            //     this.deleteEntry(message, '/api/excursions/rates/delete', {id: id})
            //         .then(() => {
            //             this.data.data = this.data.data.filter(rate => rate['id'] !== id);
            //         });
        //     console.log(rate, excursion);
        // },

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
            this.data.data.some((excursion, key) => {
                if (excursion['id'] === payload.excursion_id) {
                    excursion['rates'].some((rate, rate_key) => {
                        if (rate['id'] === payload.rate['id']) {
                            this.data.data[key]['rates'][rate_key] = payload.rate;
                            return true;
                        }
                        return false;
                    });
                    return true;
                }
                return false;
            });
            this.$refs.popup.hide();
        },
    }
}
</script>
