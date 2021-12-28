<template>
    <div>
        <container w-50 mt-30>
            <value :title="'Номер рейса'">{{ datasource.data['id'] }}</value>
            <value :title="'Отправление'">{{ datasource.data['start_at'] }}</value>
            <value :title="'Прибытие'">{{ datasource.data['end_at'] }}</value>
            <value :title="'Время в пути'">{{ datasource.data['duration'] }} мин.</value>
        </container>

        <container w-50 mt-30>
            <value :title="'Причал отправления'">{{ datasource.data['start_pier'] }}</value>
            <value :title="'Причал прибытия'">{{ datasource.data['end_pier'] }}</value>
            <value :title="'Теплоход'">{{ datasource.data['ship'] }}</value>
            <value :title="'Экскурсия'">{{ datasource.data['excursion'] }}</value>
        </container>

        <container w-50 mt-30>
            <value :title="'Статус движения'">
                <span class="link" v-if="editable" @click="statusChange">{{ datasource.data['status'] }}</span>
                <span v-else>{{ datasource.data['status'] }}</span>
            </value>
            <value :title="'Статус продаж'">
                <span class="link" v-if="editable" @click="saleStatusChange">{{ datasource.data['sale_status'] }}</span>
                <span v-else>{{ datasource.data['sale_status'] }}</span>
            </value>
        </container>

        <container w-50 mt-30>
            <value :title="'Общее количество билетов'">
                <span class="link" v-if="editable" @click="ticketsCountChange">{{ datasource.data['tickets_count'] }}</span>
                <span v-else>{{ datasource.data['tickets_count'] }}</span>
            </value>
            <value :title="'Выкуплено'">0</value>
            <value :title="'Забронировано'">0</value>
            <value :title="'Свободно'">{{ datasource.data['tickets_count'] }}</value>
        </container>

        <container w-50 mt-30>
            <value :title="'Скидки от базовой цены на кассах'">
                <span class="link" v-if="editable" @click="discountStatusChange">{{ datasource.data['discount_status'] }}</span>
                <span v-else>{{ datasource.data['discount_status'] }}</span>
            </value>
            <value :title="'Время аннулирования брони на рейс, за Х мин.'">
                <span class="link" v-if="editable" @click="cancellationTimeChange">{{ datasource.data['cancellation_time'] }}</span>
                <span v-else>{{ datasource.data['cancellation_time'] }}</span>
            </value>
            <hint mt-5 mb-10>При значении 0 бронь будет сохраняться до отправки рейса.</hint>
        </container>

        <container mt-30 v-if="editable">
            <base-link-button :to="{ name: 'trip-edit', params: { id: tripId }}">Редактировать</base-link-button>
        </container>

        <pop-up ref="status_popup" :title="popup_title"
                :buttons="[{result: 'no', caption: 'Отмена', color: 'white'}, {result: 'yes', caption: 'OK', color: 'green'}]"
                :manual="true"
        >
            <dictionary-drop-down :dictionary="popup_dictionary" v-model="current_status" :name="'status'" :original="initial_status"/>
        </pop-up>

        <pop-up ref="form_popup" v-if="editable" :title="form_popup_title"
                :buttons="[{result: 'no', caption: 'Отмена', color: 'white'}, {result: 'yes', caption: 'OK', color: 'green'}]"
                :manual="true"
        >
            <data-field-input :datasource="form" :name="'input'" :type="'number'"/>
        </pop-up>

    </div>
</template>

<script>
import Container from "../../../Components/GUI/Container";
import Value from "../../../Components/GUI/Value";
import Activity from "../../../Components/Activity";
import ValueArea from "../../../Components/GUI/ValueArea";
import BaseLinkButton from "../../../Components/Base/BaseLinkButton";
import PopUp from "../../../Components/PopUp";
import DictionaryDropDown from "../../../Components/Dictionary/DictionaryDropDown";
import Hint from "../../../Components/GUI/Hint";
import DataFieldInput from "../../../Components/DataFields/DataFieldInput";
import formDataSource from "../../../Helpers/Core/formDataSource";
import {parseRules} from "../../../Helpers/Core/validator/validator";

export default {
    props: {
        tripId: {type: Number, required: true},
        datasource: {type: Object},
        editable: {type: Boolean, default: false},
    },

    components: {
        DataFieldInput,
        Hint,
        Container,
        Value,
        Activity,
        ValueArea,
        BaseLinkButton,
        PopUp,
        DictionaryDropDown,
    },

    data: () => ({
        popup_title: null,
        popup_dictionary: null,
        initial_status: null,
        current_status: null,
        form: null,
        form_popup_title: null,
    }),

    methods: {
        statusChange() {
            this.popup_title = 'Статус движения';
            this.popup_dictionary = 'trip_statuses';
            this.initial_status = Number(this.datasource.data['status_id']);
            this.current_status = this.initial_status;
            this.genericStatusChange('/api/trips/status')
                .then(data => {
                    this.datasource.data['status'] = data['status'];
                    this.datasource.data['status_id'] = data['status_id'];
                });
        },

        saleStatusChange() {
            this.popup_title = 'Статус продаж';
            this.popup_dictionary = 'trip_sale_statuses';
            this.initial_status = Number(this.datasource.data['sale_status_id']);
            this.current_status = this.initial_status;
            this.genericStatusChange('/api/trips/sale-status')
                .then(data => {
                    this.datasource.data['sale_status'] = data['status'];
                    this.datasource.data['sale_status_id'] = data['status_id'];
                });
        },

        discountStatusChange() {
            this.popup_title = 'Скидки от базовой цены';
            this.popup_dictionary = 'trip_discount_statuses';
            this.initial_status = Number(this.datasource.data['discount_status_id']);
            this.current_status = this.initial_status;
            this.genericStatusChange('/api/trips/discount-status')
                .then(data => {
                    this.datasource.data['discount_status'] = data['status'];
                    this.datasource.data['discount_status_id'] = data['status_id'];
                });
        },

        ticketsAfterSave(payload) {
            this.datasource.data['tickets_count'] = payload['tickets_count'];
            this.form.loaded = false;
            this.$refs.form_popup.hide();
        },

        timeAfterSave(payload) {
            this.datasource.data['cancellation_time'] = payload['cancellation_time'];
            this.form.loaded = false;
            this.$refs.form_popup.hide();
        },

        ticketsCountChange() {
            this.form_popup_title = 'Общее количество билетов';
            this.form = formDataSource(null, '/api/trips/tickets-count', {id: this.tripId});
            const input = Number(this.datasource.data['tickets_count']);
            this.form.titles = {input: 'Общее количество билетов'};
            this.form.values = {input: input};
            this.form.originals = {input: input};
            this.form.validation_rules = {input: parseRules('required|integer|min:1|bail')};
            this.form.afterSave = this.ticketsAfterSave;
            this.form.toaster = this.$toast;
            this.form.loaded = true;

            this.$refs.form_popup.show()
                .then(result => {
                    if (result === 'yes') {
                        this.$refs.form_popup.process(true);
                        this.form.save();
                    } else {
                        this.$refs.form_popup.hide();
                    }
                });
        },

        cancellationTimeChange() {
            this.form_popup_title = 'Время аннулирования брони';
            this.form = formDataSource(null, '/api/trips/cancellation-time', {id: this.tripId});
            const input = Number(this.datasource.data['cancellation_time']);
            this.form.titles = {input: 'Время аннулирования брони на рейс, мин.'};
            this.form.values = {input: input};
            this.form.originals = {input: input};
            this.form.validation_rules = {input: parseRules('required|integer|min:0|bail')};
            this.form.afterSave = this.timeAfterSave;
            this.form.toaster = this.$toast;
            this.form.loaded = true;

            this.$refs.form_popup.show()
                .then(result => {
                    if (result === 'yes') {
                        this.$refs.form_popup.process(true);
                        this.form.save();
                    } else {
                        this.$refs.form_popup.hide();
                    }
                });
        },

        genericStatusChange(url) {
            return new Promise((resolve, reject) => {
                this.$refs.status_popup.show()
                    .then(result => {
                        if (result === 'yes') {
                            this.$refs.status_popup.process(true);
                            axios.post(url, {id: this.tripId, status_id: this.current_status})
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
    }
}
</script>
