<template>
    <div>
        <GuiContainer w-50 mt-30 inline>
            <GuiValue :title="'Название'">{{ data['name'] }}</GuiValue>
            <GuiValue :title="'Тип программы'">{{ data['programs'] ? data['programs'].join(', ') : '' }}</GuiValue>
            <GuiValue :title="'Продолжительность'"><span v-if="data['duration']">{{ data['duration'] }} минут</span></GuiValue>
            <GuiValue :title="'Статус'">
                <span class="link" v-if="editable" @click="statusChange"><GuiActivityIndicator :active="data['active']"/>{{ data['status'] }}</span>
                <span v-else><GuiActivityIndicator :active="data['active']"/>{{ data['status'] }}</span>
            </GuiValue>
        </GuiContainer>

        <GuiContainer w-50 mt-30 inline pl-20 v-if="data['images'] && data['images'][0]">
            <img class="w-100" :src="data['images'][0]" :alt="data['name']"/>
        </GuiContainer>

        <GuiContainer w-100 mt-30>
            <GuiValueArea :title="'Краткое описание экскурсии'" v-text="data['announce']"/>
            <GuiValueArea :title="'Полное описание экскурсии'" v-text="data['description']"/>
        </GuiContainer>

        <GuiContainer mt-15 v-if="editable">
            <GuiButton @click="edit">Редактировать</GuiButton>
        </GuiContainer>

        <FormPopUp :title="'Статус экскурсии'"
                   :form="form"
                   :options="{id: excursionId}"
                   ref="popup"
        >
            <GuiContainer w-350px>
                <FormDictionary :form="form" :name="'value'" :dictionary="'excursion_statuses'" :fresh="true" :hide-title="true"/>
            </GuiContainer>
        </FormPopUp>
    </div>
</template>

<script>


import GuiContainer from "@/Components/GUI/GuiContainer";
import GuiValue from "@/Components/GUI/GuiValue";
import GuiActivityIndicator from "@/Components/GUI/GuiActivityIndicator";
import GuiValueArea from "@/Components/GUI/GuiValueArea";
import form from "@/Core/Form";
import GuiButton from "@/Components/GUI/GuiButton";
import FormPopUp from "@/Components/FormPopUp";
import FormDictionary from "@/Components/Form/FormDictionary";

export default {
    props: {
        excursionId: {type: Number, required: true},
        data: {type: Object},
        editable: {type: Boolean, default: false},
    },

    components: {
        FormDictionary,
        FormPopUp,
        GuiButton,
        GuiValueArea,
        GuiActivityIndicator,
        GuiValue,
        GuiContainer

    },

    data: () => ({
        form: form(null, '/api/excursions/properties'),
    }),

    methods: {
        edit() {
            this.$router.push({name: 'excursion-edit', params: {id: this.excursionId}});
        },
        statusChange() {
            this.form.reset();
            this.form.set('name', 'status_id');
            this.form.set('value', this.data['status_id'], 'required', 'Статус экскурсии', true);
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
