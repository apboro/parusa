<template>
    <LayoutPage :loading="processing" :title="form.payload['name']"
                :breadcrumbs="[{caption: 'Промокоды', to: {name: 'promo-code'}}]"
                :link="{name: 'excursion-list'}"
                :link-title="'К списку промокодов'"
    >
        <GuiContainer mt-30>
            <FormString :form="form" :name="'name'"/>
            <FormString :form="form" :name="'code'"/>
            <FormNumber :form="form" :name="'amount'"/>
            <FormDictionary :form="form" :name="'status_id'" :dictionary="'excursion_statuses'"/>
            <FormDictionary :form="form" :name="'excursions'" :dictionary="'excursions'" :fresh="true" :multi="true" :search="true"/>
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


export default {
    components: {
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
    }),

    computed: {
        excursionId() {
            return Number(this.$route.params.id);
        },
        processing() {
            return this.form.loading || this.form.saving;
        },
    },

    created() {
        this.form.toaster = this.$toast;
        this.form.load({id: this.excursionId});
    },

    methods: {
        save() {
            if (!this.form.validate()) {
                return;
            }
            this.form.save({id: this.excursionId})
                .then(response => {
                    this.$router.push({name: 'promo-code'});
                })
        },
        cancel() {
            this.$router.push({name: 'promo-code'});
        },
    }
}
</script>
