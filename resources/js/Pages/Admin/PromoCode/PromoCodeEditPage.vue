<template>
    <LayoutPage :loading="processing" :title="form.payload['name']"
                :breadcrumbs="[{caption: 'Промокоды', to: {name: 'promo-code-list'}}]"
                :link="{name: 'excursion-list'}"
                :link-title="'К списку промокодов'"
    >
        <GuiContainer mt-30>
            <FormString :form="form" :name="'name'"/>
            <FormString :form="form" :name="'code'"/>
            <FormNumber :form="form" :name="'amount'"/>
            <FormDictionary :form="form" :name="'status_id'" :dictionary="'excursion_statuses'"/>

            <GuiValueArea mt-30 :title="'Экскурсии'">
                <template v-for="excursion in form.payload['excursions']">
                    <InputCheckbox v-model="arrExcursions" :value="excursion.id" :label="excursion.name"/>
                </template>
            </GuiValueArea>
        </GuiContainer>

        <GuiContainer mt-30>
            <GuiButton @click="save" :color="'green'">Сохранить</GuiButton>
            <GuiButton @click="cancel">Отмена</GuiButton>
        </GuiContainer>
    </LayoutPage>
</template>

<script>
import BaseButton from "../../../Components/GUI/GuiButton";
import LayoutPage from "@/Components/Layout/LayoutPage";
import GuiContainer from "@/Components/GUI/GuiContainer";
import GuiButton from "@/Components/GUI/GuiButton";
import form from "@/Core/Form";
import FormString from "@/Components/Form/FormString";
import FormDictionary from "@/Components/Form/FormDictionary";
import FormImages from "@/Components/Form/FormImages";
import FormNumber from "@/Components/Form/FormNumber";
import FormText from "@/Components/Form/FormText";
import FormCheckBox from "@/Components/Form/FormCheckBox";
import InputCheckbox from "@/Components/Inputs/InputCheckbox.vue";
import FieldWrapper from "@/Components/Fields/Helpers/FieldWrapper.vue";
import GuiValueArea from "../../../Components/GUI/GuiValueArea";


export default {
    components: {
        GuiValueArea,
        FieldWrapper,
        InputCheckbox,
        FormText,
        FormNumber,
        FormImages,
        FormCheckBox,
        FormDictionary,
        FormString,
        GuiButton,
        GuiContainer,
        LayoutPage,
        BaseButton,
    },

    data: () => ({
        form: form('/api/promo-code/get', '/api/promo-code/update'),
        arrExcursions: [],
    }),

    computed: {
        promoCodeId() {
            return Number(this.$route.params.id);
        },
        processing() {
            return this.form.is_loading || this.form.is_saving;
        },
    },

    created() {
        this.form.toaster = this.$toast;
        this.form.load({id: this.promoCodeId});
    },

    methods: {
        save() {
            this.form.set('excursions', this.arrExcursions);

            if (!this.form.validate()) {
                return;
            }
            this.form.save({id: this.promoCodeId})
                .then(() => {
                    this.$router.push({name: 'promo-code-list'});
                })
        },
        cancel() {
            this.$router.push({name: 'promo-code-list'});
        },
    }
}
</script>
