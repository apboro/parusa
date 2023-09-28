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

    </div>
</template>

<script>
import GuiContainer from "@/Components/GUI/GuiContainer";
import GuiValue from "@/Components/GUI/GuiValue";
import GuiValueArea from "@/Components/GUI/GuiValueArea";
import GuiButton from "@/Components/GUI/GuiButton";
import GuiActivityIndicator from "@/Components/GUI/GuiActivityIndicator";
import GuiHint from "@/Components/GUI/GuiHint";
import GuiFilesList from "@/Components/GUI/GuiFilesList";


export default {
    props: {
        partnerId: {type: Number, required: true},
        data: {type: Object},
        editable: {type: Boolean, default: false},
    },

    emits: ['update'],

    components: {
        GuiFilesList,
        GuiHint,
        GuiActivityIndicator,
        GuiButton,
        GuiValueArea,
        GuiValue,
        GuiContainer
    },


}
</script>
