<template>
    <LayoutPage :loading="processing" :title="form.payload['name']"
                :breadcrumbs="[{caption: 'Каталог экскурсий', to: {name: 'excursion-list'}}]"
                :link="{name: 'excursion-list'}"
                :link-title="'К списку экскурсий'"
    >
        <GuiContainer mt-30>
            <FormString :form="form" :name="'name'"/>
            <FormString :form="form" :name="'name_receipt'"/>
            <FormDictionary :form="form" :name="'status_id'" :dictionary="'excursion_statuses'"/>
            <FormDictionary :form="form" :name="'provider_id'" :dictionary="'providers'"/>
            <FormCheckBox :form="form" :name="'only_site'" :hide-title="true"/>
            <FormCheckBox :form="form" :name="'use_seat_scheme'" :hide-title="true"/>
            <FormCheckBox :form="form" :name="'is_single_ticket'" :hide-title="true"/>
<!--            <FormCheckBox v-model="showReverseExcursion" onchange="showReverseExcursion = !showReverseExcursion" :form="form" :name="'Билет туда и обратно'" :hide-title="true"/>-->
            <FormDictionary :form="form" :name="'reverse_excursion_id'" :dictionary="'excursions'" :search="true" :fresh="true" :placeholder="'Нет'" :has-null="true"/>
            <FormDictionary :form="form" :name="'excursion_type_id'" :dictionary="'excursion_types'" :fresh="true" :placeholder="'Нет'" :has-null="true"/>


            <FormImages :form="form" :name="'images'"/>
            <FormDictionary :form="form" :name="'programs'" :dictionary="'excursion_programs'" :multi="true" :fresh="true"/>
            <FormNumber :form="form" :name="'duration'"/>
            <FormText :form="form" :name="'announce'"/>
            <FormText :form="form" :name="'description'"/>
            <FormImages :form="form" :name="'trip_images'"/>
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
        showReverseExcursion: false,
        form: form('/api/excursions/get', '/api/excursions/update'),
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
                    if (this.excursionId === 0) {
                        this.$router.push({name: 'excursion-view', params: {id: response.payload['id']}});
                    } else {
                        this.$router.push({name: 'excursion-view', params: {id: this.excursionId}});
                    }
                })
        },
        cancel() {
            if (this.excursionId === 0) {
                this.$router.push({name: 'excursion-list'});
            } else {
                this.$router.push({name: 'excursion-view', params: {id: this.excursionId}})
            }
        },
    }
}
</script>
