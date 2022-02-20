<template>
    <div>
        <GuiContainer w-50 mt-30 inline>
            <GuiValue :title="'Название'">{{ data['name'] }}</GuiValue>
            <GuiValue :title="'Адрес причала'">{{ data['address'] }}</GuiValue>
            <GuiValue :title="'Время работы'">{{ data['work_time'] }}</GuiValue>
            <GuiValue :title="'Телефон'">{{ data['phone'] }}</GuiValue>
            <GuiValue :title="'Координаты причала'">
                <span v-if="data['latitude'] && data['longitude']">{{ data['latitude'] }}, {{ data['longitude'] }}</span>
            </GuiValue>
            <GuiValue :title="'Статус'">
                <span class="link" v-if="editable" @click="statusChange"><GuiActivityIndicator :active="data['active']"/>{{ data['status'] }}</span>
                <span v-else><GuiActivityIndicator :active="data['active']"/>{{ data['status'] }}</span>
            </GuiValue>
        </GuiContainer>

        <GuiContainer w-50 mt-30 inline pl-20 v-if="data['images'] && data['images'][0]">
            <img class="w-100" :src="data['images'][0]" :alt="data['name']"/>
        </GuiContainer>

        <GuiContainer w-100 mt-30>
            <GuiValueArea :title="'Описание причала'" v-text="data['description']"/>
            <GuiValueArea :title="'Как добраться'" v-text="data['way_to']"/>
        </GuiContainer>

        <GuiContainer mt-15 v-if="editable">
            <GuiButton @click="edit">Редактировать</GuiButton>
        </GuiContainer>

        <FormPopUp :title="'Статус причала'"
                   :form="form"
                   :options="{id: pierId}"
                   ref="popup"
        >
            <GuiContainer w-350px>
                <FormDictionary :form="form" :name="'value'" :dictionary="'pier_statuses'" :fresh="true" :hide-title="true"/>
            </GuiContainer>
        </FormPopUp>
    </div>
</template>

<script>
import GuiContainer from "@/Components/GUI/GuiContainer";
import GuiValue from "@/Components/GUI/GuiValue";
import GuiActivityIndicator from "@/Components/GUI/GuiActivityIndicator";
import GuiValueArea from "@/Components/GUI/GuiValueArea";
import GuiButton from "@/Components/GUI/GuiButton";
import FormPopUp from "@/Components/FormPopUp";
import FormDictionary from "@/Components/Form/FormDictionary";
import form from "@/Core/Form";

export default {
    props: {
        pierId: {type: Number, required: true},
        data: {type: Object},
        editable: {type: Boolean, default: false},
    },

    emits: ['update'],

    components: {
        FormDictionary,
        FormPopUp,
        GuiContainer,
        GuiValue,
        GuiValueArea,
        GuiActivityIndicator,
        GuiButton,

    },

    data: () => ({
        form: form(null, '/api/piers/properties'),
    }),

    methods: {
        edit() {
            this.$router.push({name: 'pier-edit', params: {id: this.pierId}});
        },
        statusChange() {
            this.form.reset();
            this.form.set('name', 'status_id');
            this.form.set('value', this.data['status_id'], 'required', 'Статус причала', true);
            this.form.toaster = this.$toast;
            this.form.load();
            this.$refs.popup.show()
                .then(response => {
                    this.$emit('update', response.payload);
                })
        },
    }
}
</script>
