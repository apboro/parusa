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
            <GuiButton @clicked="edit" v-if="!blocked">Редактировать</GuiButton>
            <GuiText mt-10 v-if="blocked">
                <div class="mb-5 mt-50" style="font-size:14px">* Рейс нельзя редактировать, на него есть оформленные билеты.</div>
            </GuiText>
        </GuiContainer>

        <FormPopUp :title="form_title"
                   :form="form"
                   :options="{id: tripId}"
                   ref="popup"
        >
            <GuiContainer w-350px>
                <FormDictionary v-if="dictionary !== null" :form="form" :name="'value'" :dictionary="dictionary" :fresh="true" :hide-title="true"/>
                <FormNumber v-else :form="form" :name="'value'" :hide-title="true"/>
            </GuiContainer>
        </FormPopUp>
    </div>
</template>

<script>
import form from "@/Core/Form";
import GuiContainer from "@/Components/GUI/GuiContainer";
import GuiValue from "@/Components/GUI/GuiValue";
import GuiHint from "@/Components/GUI/GuiHint";
import GuiButton from "@/Components/GUI/GuiButton";
import FormPopUp from "@/Components/FormPopUp";
import FormDictionary from "@/Components/Form/FormDictionary";
import FormNumber from "@/Components/Form/FormNumber";
import GuiText from "../../../../Components/GUI/GuiText";

export default {
    props: {
        tripId: {type: Number, required: true},
        data: {type: Object},
        editable: {type: Boolean, default: false},
    },

    emits: ['update'],

    components: {
        GuiText,
        FormNumber,
        FormDictionary,
        FormPopUp,
        GuiButton,
        GuiHint,
        GuiValue,
        GuiContainer,
    },

    computed: {
        blocked() {
            return this.data['tickets_sold'] || this.data['tickets_reserved'];
        }
    },

    data: () => ({
        form: form(null, '/api/trips/properties'),
        form_title: null,
        dictionary: null,
    }),

    methods: {
        edit() {
            this.$router.push({name: 'trip-edit', params: {id: this.tripId}});
        },
        showForm(title, key, rules, dictionary = null) {
            this.form_title = title;
            this.form.reset();
            this.form.set('name', key);
            this.form.set('value', this.data[key], rules, title, true);
            this.dictionary = dictionary;
            this.form.toaster = this.$toast;
            this.form.load();
            this.$refs.popup.show()
                .then(response => {
                    this.$emit('update', response.payload);
                })
        },
        statusChange() {
            this.showForm('Статус движения', 'status_id', 'required', 'trip_statuses');
        },
        saleStatusChange() {
            this.showForm('Статус продаж', 'sale_status_id', 'required', 'trip_sale_statuses');
        },
        ticketsCountChange() {
            this.showForm('Общее количество билетов', 'tickets_total', 'required|integer|min:0');
        },
        discountStatusChange() {
            this.showForm('Скидки от базовой цены на кассах', 'discount_status_id', 'required', 'trip_discount_statuses');
        },
        cancellationTimeChange() {
            this.showForm('Время аннулирования брони, за Х мин.', 'cancellation_time', 'required|integer|min:0');
        },
    }
}
</script>
