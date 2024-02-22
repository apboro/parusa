<template>
    <div>
        <GuiContainer w-50 mt-30>
            <GuiValue :title="'Название'">{{ data['name'] }}</GuiValue>
            <GuiValue :title="'Вместимость'">{{ data['capacity'] }}</GuiValue>
            <GuiValue v-if="data['partner']" :title="'Теплоход партнёра'">{{ data['partner'] }}</GuiValue>
            <GuiValue v-else :title="'Владелец'">{{ data['owner'] }}</GuiValue>
            <GuiValue :title="'Описание'">{{ data['description'] }}</GuiValue>
            <GuiValue :title="'Статус'">
                <span class="link" v-if="editable" @click="statusChange"><GuiActivityIndicator :active="data['active']"/>{{ data['status'] }}</span>
                <span v-else><GuiActivityIndicator :active="data.active"/>{{ data['status'] }}</span>
            </GuiValue>
            <GuiValue :title="'Схема рассадки'">{{data['ship_has_seats_scheme'] ? 'Есть' : 'Нет' }}</GuiValue>
        </GuiContainer>

        <GuiContainer mt-20 t-15 v-if="editable">
            <GuiButton v-if="data['provider_id'] === 5" @clicked ="edit">Редактировать</GuiButton>
        </GuiContainer>

        <FormPopUp :title="'Статус теплохода'"
                   :form="form"
                   :options="{id: shipId}"
                   ref="popup"
        >
            <GuiContainer w-350px>
                <FormDictionary :form="form" :name="'value'" :dictionary="'ships_statuses'" :hide-title="true"/>
            </GuiContainer>
        </FormPopUp>
    </div>
</template>

<script>
import GuiContainer from "@/Components/GUI/GuiContainer";
import GuiValue from "@/Components/GUI/GuiValue";
import GuiValueArea from "@/Components/GUI/GuiValueArea";
import GuiActivityIndicator from "@/Components/GUI/GuiActivityIndicator";
import GuiButton from "@/Components/GUI/GuiButton";
import FormPopUp from "@/Components/FormPopUp";
import FormDictionary from "@/Components/Form/FormDictionary";
import form from "@/Core/Form";

export default {
    props: {
        shipId: {type: Number, required: true},
        data: {type: Object, required: true},
        editable: {type: Boolean, default: false},
    },

    emits: ['update'],

    components: {
        FormDictionary,
        FormPopUp,
        GuiButton,
        GuiActivityIndicator,
        GuiValueArea,
        GuiValue,
        GuiContainer
    },

    data: () => ({
        form: form(null, '/api/ship/properties'),
    }),

    methods: {
        edit() {
            this.$router.push({name: 'ship-edit', params: {id: this.shipId}});
        },
        statusChange() {
            this.form.reset();
            this.form.set('name', 'status_id');
            this.form.set('value', this.data['status_id'], 'required', 'Статус теплохода', true);
            this.form.toaster = this.$toast;
            this.form.load();
            this.$refs.popup.show()
                .then(response => {
                    this.$emit('update', response.payload);
                });
        },
    }
}
</script>
