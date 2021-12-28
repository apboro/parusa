<template>
    <div>
        <container w-50 mt-30>
            <value :title="'Название партнера'">{{ datasource.data['name'] }}</value>
            <value :title="'Дата заведения'">{{ datasource.data['created_at'] }}</value>
            <value :title="'Тип партнера'">{{ datasource.data['type'] }}</value>
            <value :title="'Статус'">
                <span class="link" v-if="editable" @click="statusChange"><activity :active="datasource.data.active"/>{{ datasource.data.status }}</span>
                <span v-else><activity :active="datasource.data.active"/>{{ datasource.data.status }}</span>
            </value>
        </container>
        <container w-50 mt-30>
            <value :title="'Билеты для гидов'">
                <span class="link" v-if="editable" @click="ticketsChange">{{ datasource.data['tickets_for_guides'] }}</span>
                <span v-else>{{ datasource.data['tickets_for_guides'] }}</span>
            </value>
            <hint mt-5 mb-10>
                При значении "0" партнер не может включать в заказ бесплатные билеты для гидов. Любое положительное число разрешает данную возможность и определяет
                максимальное количество таких билетов для одного заказа. Например, при значении "1" к заказу можно будет добавить 1 билет для гида.
            </hint>
            <value :title="'Бронирование билетов'">
                <span class="link" v-if="editable" @click="reserveChange">{{ datasource.data['can_reserve_tickets'] === 1 ? 'Разрешено' : 'Запрещено' }}</span>
                <span v-else>{{ datasource.data['can_reserve_tickets'] }}</span>
            </value>
        </container>

        <container w-100 mt-50>
            <value-area :title="'Документы'"/>
        </container>

        <container w-100 mt-50>
            <value-area :title="'Заметки'" v-text="datasource.data['notes']"/>
        </container>

        <container mt-15 v-if="editable">
            <base-link-button :to="{ name: 'partners-edit', params: { id: partnerId }}">Редактировать</base-link-button>
        </container>


        <pop-up ref="popup" v-if="editable"
                :title="'Изменить статус партнёра'"
                :buttons="[{result: 'no', caption: 'Отмена', color: 'white'}, {result: 'yes', caption: 'OK', color: 'green'}]"
                :manual="true"
        >
            <dictionary-drop-down :dictionary="'partner_statuses'" :name="'status'" :original="initial_status" v-model="current_status"/>
        </pop-up>

        <pop-up ref="popup_tickets" v-if="editable"
                :title="'Билеты для гидов'"
                :buttons="[{result: 'no', caption: 'Отмена', color: 'white'}, {result: 'yes', caption: 'OK', color: 'green'}]"
                :resolving="ticketsFormResolving"
                :manual="true"
        >
            <data-field-input :datasource="form" :name="'tickets_for_guides'" @changed="ticketsChanged"/>
        </pop-up>

        <pop-up ref="popup_reserve" v-if="editable"
                :title="'Бронирование билетов'"
                :buttons="[{result: 'no', caption: 'Отмена', color: 'white'}, {result: 'yes', caption: 'OK', color: 'green'}]"
                :manual="true"
        >
            <base-drop-down :name="'can_reserve_tickets'" :key-by="'id'" :value-by="'name'"
                            v-model="current_can_reserve_tickets"
                            :original="datasource.data['can_reserve_tickets']"
                            :options="[
                                {id: 0, name: 'Запрещено'},
                                {id: 1, name: 'Разрешено'},
                            ]" :placeholder="'Бронирование билетов'"
            />
        </pop-up>
    </div>
</template>

<script>
import formDataSource from "../../../Helpers/Core/formDataSource";
import {parseRules} from "../../../Helpers/Core/validator/validator";

import Container from "../../../Components/GUI/Container";
import Value from "../../../Components/GUI/Value";
import Activity from "../../../Components/Activity";
import Hint from "../../../Components/GUI/Hint";
import ValueArea from "../../../Components/GUI/ValueArea";
import BaseLinkButton from "../../../Components/Base/BaseLinkButton";
import PopUp from "../../../Components/PopUp";
import DictionaryDropDown from "../../../Components/Dictionary/DictionaryDropDown";
import DataFieldInput from "../../../Components/DataFields/DataFieldInput";
import BaseDropDown from "../../../Components/Base/BaseDropDown";

export default {
    props: {
        partnerId: {type: Number, required: true},
        datasource: {type: Object},
        editable: {type: Boolean, default: false},
    },

    components: {
        Container,
        Value,
        Activity,
        Hint,
        ValueArea,
        BaseLinkButton,
        PopUp,
        DictionaryDropDown,
        DataFieldInput,
        BaseDropDown,
    },

    data: () => ({
        initial_status: null,
        current_status: null,
        current_can_reserve_tickets: null,
        form: null,
    }),

    methods: {
        statusChange() {
            this.initial_status = Number(this.datasource.data.status_id);
            this.current_status = this.initial_status;
            this.$refs.popup.show()
                .then(result => {
                    if (result === 'yes') {
                        this.$refs.popup.process(true);
                        axios.post('/api/partners/status', {id: this.partnerId, status_id: this.current_status})
                            .then(response => {
                                this.$toast.success(response.data.message, 3000);
                                this.datasource.data.status = response.data.data.status;
                                this.datasource.data.status_id = response.data.data.status_id;
                                this.datasource.data.active = response.data.data.active;
                            })
                            .catch(error => {
                                this.$toast.error(error.response.data.message);
                            })
                            .finally(() => {
                                this.$refs.popup.hide();
                            })
                    } else {
                        this.$refs.popup.hide();
                    }
                });
        },

        ticketsFormResolving(result) {
            return result !== 'yes' || this.form.validateAll();
        },

        ticketsAfterSave(payload) {
            this.datasource.data['tickets_for_guides'] = payload['tickets_for_guides'];
            this.form.loaded = false;
            this.$refs.popup_tickets.hide();
        },

        ticketsChanged(name, value) {
            if (!isNaN(Number(value))) {
                this.form.validate(name, Number(value));
            }
        },

        ticketsChange() {
            this.form = formDataSource(null, '/api/partners/guides-tickets', {id: this.partnerId});
            this.form.values = {tickets_for_guides: String(this.datasource.data['tickets_for_guides'])};
            this.form.originals = {tickets_for_guides: String(this.datasource.data['tickets_for_guides'])};
            this.form.validation_rules = {tickets_for_guides: parseRules('required|integer|min:0|bail')};
            this.form.afterSave = this.ticketsAfterSave;
            this.form.toaster = this.$toast;
            this.form.titles = {tickets_for_guides: 'Билеты для гидов'};
            this.form.loaded = true;

            this.$refs.popup_tickets.show()
                .then(result => {
                    if (result === 'yes') {
                        this.$refs.popup_tickets.process(true);
                        this.form.save();
                    } else {
                        this.$refs.popup_tickets.hide();
                    }
                });
        },

        reserveChange() {
            this.current_can_reserve_tickets = Number(this.datasource.data['can_reserve_tickets']);
            this.$refs.popup_reserve.show()
                .then(result => {
                    if (result === 'yes') {
                        this.$refs.popup_reserve.process(true);
                        axios.post('/api/partners/reservable', {id: this.partnerId, can_reserve_tickets: this.current_can_reserve_tickets})
                            .then(response => {
                                this.$toast.success(response.data.message, 3000);
                                this.datasource.data['can_reserve_tickets'] = response.data.data.can_reserve_tickets;
                            })
                            .catch(error => {
                                this.$toast.error(error.response.data.message);
                            })
                            .finally(() => {
                                this.$refs.popup_reserve.hide();
                            })
                    } else {
                        this.$refs.popup_reserve.hide();
                    }
                });
        },
    }
}
</script>
