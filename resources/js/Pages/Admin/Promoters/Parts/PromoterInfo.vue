<template>
    <div>
        <GuiContainer w-50 mt-30>
            <GuiValue :title="'ФИО промоутера'">{{ data['name'] }}</GuiValue>
            <GuiValue :title="'ID'">{{ data['id'] }}</GuiValue>
            <GuiValue :title="'Дата заведения'" v-if="editable">{{ data['created_at'] }}</GuiValue>
            <GuiValue :title="'Телефон'" v-if="editable">{{ data['phone'] }}</GuiValue>
            <GuiValue :title="'Почта'" v-if="editable">{{ data['email'] }}</GuiValue>
        </GuiContainer>

        <GuiContainer w-100 mt-20 v-if="editable">
            <GuiValueArea :title="'Заметки'" v-text="data['notes']"/>
        </GuiContainer>

        <GuiContainer mt-15 v-if="editable">
            <GuiButton @click="edit">Редактировать</GuiButton>
        </GuiContainer>

    </div>
</template>

<script>
import form from "@/Core/Form";
import GuiContainer from "@/Components/GUI/GuiContainer";
import GuiValue from "@/Components/GUI/GuiValue";
import GuiValueArea from "@/Components/GUI/GuiValueArea";
import GuiButton from "@/Components/GUI/GuiButton";
import GuiActivityIndicator from "@/Components/GUI/GuiActivityIndicator";
import GuiHint from "@/Components/GUI/GuiHint";
import GuiFilesList from "@/Components/GUI/GuiFilesList";
import FormPopUp from "@/Components/FormPopUp";
import FormDictionary from "@/Components/Form/FormDictionary";
import FormNumber from "@/Components/Form/FormNumber";
import FormDropdown from "@/Components/Form/FormDropdown";

export default {
    props: {
        partnerId: {type: Number, required: true},
        data: {type: Object},
        editable: {type: Boolean, default: false},
    },

    emits: ['update'],

    components: {
        FormDropdown,
        FormNumber,
        FormDictionary,
        FormPopUp,
        GuiFilesList,
        GuiHint,
        GuiActivityIndicator,
        GuiButton,
        GuiValueArea,
        GuiValue,
        GuiContainer

    },

    data: () => ({
        form: form(null, '/api/promoters/update'),
        form_title: null,
        dictionary: null,
    }),

    methods: {
        edit() {
            this.$router.push({name: 'promoters-edit', params: {id: this.partnerId}});
        },
    }
}
</script>
