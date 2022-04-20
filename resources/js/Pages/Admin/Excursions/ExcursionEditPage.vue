<template>
    <LayoutPage :loading="processing" :title="form.payload['name']"
                :breadcrumbs="[{caption: 'Каталог экскурсий', to: {name: 'excursion-list'}}]"
                :link="{name: 'excursion-list'}"
                :link-title="'К списку экскурсий'"
    >
        <GuiContainer mt-30>
            <FormString :form="form" :name="'name'"/>
            <FormDictionary :form="form" :name="'status_id'" :dictionary="'excursion_statuses'"/>
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

export default {
    components: {
        FormText,
        FormNumber,
        FormImages,
        FormDictionary,
        FormString,
        GuiButton,
        GuiContainer,
        LayoutPage,
        BaseButton,
    },

    data: () => ({
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
