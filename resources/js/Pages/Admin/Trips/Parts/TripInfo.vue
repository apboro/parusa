<template>
    <div>
        <GuiContainer w-50 mt-30>
            <GuiValue :title="'Номер рейса'">{{ data['id'] }}</GuiValue>
            <GuiValue :title="'Отправление'">{{ data['start_at'] }}</GuiValue>
            <GuiValue :title="'Прибытие'">{{ data['end_at'] }}</GuiValue>
            <GuiValue :title="'Время в пути'">{{ data['duration'] }} мин.</GuiValue>
        </GuiContainer>
        <GuiContainer w-50 mt-30>
            <GuiValue :title="'Причал отправления'">{{ data['start_pier'] }}</GuiValue>
            <GuiValue :title="'Причал прибытия'">{{ data['end_pier'] }}</GuiValue>
            <GuiValue :title="'Теплоход'">{{ data['ship'] }}</GuiValue>
            <GuiValue :title="'Экскурсия'">{{ data['excursion'] }}</GuiValue>
        </GuiContainer>
        <GuiContainer w-50 mt-30>
            <GuiValue :title="'Статус движения'">
                <span class="link" v-if="editable" @click="statusChange">{{ data['status'] }}</span>
                <span v-else>{{ data['status'] }}</span>
            </GuiValue>
            <GuiValue :title="'Статус продаж'">
                <span class="link" v-if="editable" @click="saleStatusChange">{{ data['sale_status'] }}</span>
                <span v-else>{{ data['sale_status'] }}</span>
            </GuiValue>
        </GuiContainer>
        <GuiContainer w-50 mt-30>
            <GuiValue :title="'Общее количество билетов'">
                <span class="link" v-if="editable" @click="ticketsCountChange">{{ data['tickets_total'] }}</span>
                <span v-else>{{ data['tickets_total'] }}</span>
            </GuiValue>
            <GuiValue :title="'Выкуплено'">{{ data['tickets_sold'] }}</GuiValue>
            <GuiValue :title="'Забронировано'">{{ data['tickets_reserved'] }}</GuiValue>
            <GuiValue :title="'Свободно'">{{ data['tickets_total'] - data['tickets_sold'] - data['tickets_reserved'] }}</GuiValue>
        </GuiContainer>
        <GuiContainer w-50 mt-30>
            <GuiValue :title="'Скидки от базовой цены на кассах'">
                <span class="link" v-if="editable" @click="discountStatusChange">{{ data['discount_status'] }}</span>
                <span v-else>{{ data['discount_status'] }}</span>
            </GuiValue>
            <GuiValue :title="'Время аннулирования брони на рейс, за Х мин.'">
                <span class="link" v-if="editable" @click="cancellationTimeChange">{{ data['cancellation_time'] }}</span>
                <span v-else>{{ data['cancellation_time'] }}</span>
            </GuiValue>
            <GuiHint mt-5 mb-10>При значении 0 бронь будет сохраняться до отправки рейса.</GuiHint>
        </GuiContainer>

        <GuiContainer mt-30 v-if="editable">
            <GuiButton @click="edit">Редактировать</GuiButton>
        </GuiContainer>

        <!--        <pop-up ref="status_popup" :title="popup_title"-->
        <!--                :buttons="[{result: 'no', caption: 'Отмена', color: 'white'}, {result: 'yes', caption: 'OK', color: 'green'}]"-->
        <!--                :manual="true"-->
        <!--        >-->
        <!--            <dictionary-drop-down :dictionary="popup_dictionary" v-model="current_status" :name="'status'" :original="initial_status"/>-->
        <!--        </pop-up>-->

        <!--        <pop-up ref="form_popup" v-if="editable" :title="form_popup_title"-->
        <!--                :buttons="[{result: 'no', caption: 'Отмена', color: 'white'}, {result: 'yes', caption: 'OK', color: 'green'}]"-->
        <!--                :manual="true"-->
        <!--        >-->
        <!--            <data-field-input :datasource="form" :name="'input'" :type="'number'"/>-->
        <!--        </pop-up>-->

    </div>
</template>

<script>

import GuiContainer from "@/Components/GUI/GuiContainer";
import GuiValue from "@/Components/GUI/GuiValue";
import GuiHint from "@/Components/GUI/GuiHint";
import GuiButton from "@/Components/GUI/GuiButton";

export default {
    props: {
        tripId: {type: Number, required: true},
        data: {type: Object},
        editable: {type: Boolean, default: false},
    },

    components: {
        GuiButton,
        GuiHint,
        GuiValue,
        GuiContainer

    },

    data: () => ({
        // popup_title: null,
        // popup_dictionary: null,
        // initial_status: null,
        // current_status: null,
        // form: null,
        // form_popup_title: null,
    }),

    methods: {
        edit() {
            this.$router.push({name: 'trip-edit', params: {id: this.tripId}});
        },
        statusChange() {
        },
        saleStatusChange() {
        },
        ticketsCountChange() {
        },
        discountStatusChange() {
        },
        cancellationTimeChange() {
        },
        // statusChange() {
        //     this.popup_title = 'Статус движения';
        //     this.popup_dictionary = 'trip_statuses';
        //     this.initial_status = Number(this.datasource.data['status_id']);
        //     this.current_status = this.initial_status;
        //     this.genericStatusChange('/api/trips/status')
        //         .then(data => {
        //             this.datasource.data['status'] = data['status'];
        //             this.datasource.data['status_id'] = data['status_id'];
        //         });
        // },
        //
        // saleStatusChange() {
        //     this.popup_title = 'Статус продаж';
        //     this.popup_dictionary = 'trip_sale_statuses';
        //     this.initial_status = Number(this.datasource.data['sale_status_id']);
        //     this.current_status = this.initial_status;
        //     this.genericStatusChange('/api/trips/sale-status')
        //         .then(data => {
        //             this.datasource.data['sale_status'] = data['status'];
        //             this.datasource.data['sale_status_id'] = data['status_id'];
        //         });
        // },
        //
        // discountStatusChange() {
        //     this.popup_title = 'Скидки от базовой цены';
        //     this.popup_dictionary = 'trip_discount_statuses';
        //     this.initial_status = Number(this.datasource.data['discount_status_id']);
        //     this.current_status = this.initial_status;
        //     this.genericStatusChange('/api/trips/discount-status')
        //         .then(data => {
        //             this.datasource.data['discount_status'] = data['status'];
        //             this.datasource.data['discount_status_id'] = data['status_id'];
        //         });
        // },
        //
        // ticketsAfterSave(payload) {
        //     this.datasource.data['tickets_total'] = payload['tickets_total'];
        //     this.form.loaded = false;
        //     this.$refs.form_popup.hide();
        // },
        //
        // timeAfterSave(payload) {
        //     this.datasource.data['cancellation_time'] = payload['cancellation_time'];
        //     this.form.loaded = false;
        //     this.$refs.form_popup.hide();
        // },
        //
        // ticketsCountChange() {
        //     this.form_popup_title = 'Общее количество билетов';
        //     this.form = formDataSource(null, '/api/trips/tickets-count', {id: this.tripId});
        //     const input = Number(this.datasource.data['tickets_total']);
        //     this.form.titles = {input: 'Общее количество билетов'};
        //     this.form.values = {input: input};
        //     this.form.originals = {input: input};
        //     this.form.validation_rules = {input: parseRules('required|integer|min:1|bail')};
        //     this.form.afterSave = this.ticketsAfterSave;
        //     this.form.toaster = this.$toast;
        //     this.form.loaded = true;
        //
        //     this.$refs.form_popup.show()
        //         .then(result => {
        //             if (result === 'yes') {
        //                 this.$refs.form_popup.process(true);
        //                 this.form.save();
        //             } else {
        //                 this.$refs.form_popup.hide();
        //             }
        //         });
        // },
        //
        // cancellationTimeChange() {
        //     this.form_popup_title = 'Время аннулирования брони';
        //     this.form = formDataSource(null, '/api/trips/cancellation-time', {id: this.tripId});
        //     const input = Number(this.datasource.data['cancellation_time']);
        //     this.form.titles = {input: 'Время аннулирования брони на рейс, мин.'};
        //     this.form.values = {input: input};
        //     this.form.originals = {input: input};
        //     this.form.validation_rules = {input: parseRules('required|integer|min:0|bail')};
        //     this.form.afterSave = this.timeAfterSave;
        //     this.form.toaster = this.$toast;
        //     this.form.loaded = true;
        //
        //     this.$refs.form_popup.show()
        //         .then(result => {
        //             if (result === 'yes') {
        //                 this.$refs.form_popup.process(true);
        //                 this.form.save();
        //             } else {
        //                 this.$refs.form_popup.hide();
        //             }
        //         });
        // },
        //
        // genericStatusChange(url) {
        //     return new Promise((resolve, reject) => {
        //         this.$refs.status_popup.show()
        //             .then(result => {
        //                 if (result === 'yes') {
        //                     this.$refs.status_popup.process(true);
        //                     axios.post(url, {id: this.tripId, status_id: this.current_status})
        //                         .then(response => {
        //                             this.$toast.success(response.data.message, 3000);
        //                             resolve(response.data.data);
        //                         })
        //                         .catch(error => {
        //                             this.$toast.error(error.response.data.message);
        //                             reject();
        //                         })
        //                         .finally(() => {
        //                             this.$refs.status_popup.hide();
        //                         })
        //                 } else {
        //                     this.$refs.status_popup.hide();
        //                 }
        //             });
        //     });
        // }
    }
}
</script>
