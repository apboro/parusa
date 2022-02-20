<template>
    <LayoutPage :loading="processing" :title="form.payload['name']"
                :breadcrumbs="[{caption: 'Причалы', to: {name: 'pier-list'}}]"
                :link="{name: 'pier-list'}"
                :link-title="'К списку причалов'"
    >
        <GuiContainer mt-30>
            <FormString :form="form" :name="'name'"/>
            <FormDictionary :form="form" :dictionary="'pier_statuses'" :name="'status_id'" :fresh="true"/>
<!--            <data-field-images :form="form" :name="'images'"/>-->
            <FormString :form="form" :name="'work_time'"/>
<!--            <data-field-masked-input :form="form" :name="'phone'" :mask="'+7 (###) ###-##-##'"/>-->
            <FormString :form="form" :name="'address'"/>
            <FormString :form="form" :name="'latitude'"/>
            <FormString :form="form" :name="'longitude'"/>
<!--            <data-field-text-area :form="form" :name="'description'"/>-->
<!--            <data-field-text-area :form="form" :name="'way_to'"/>-->
        </GuiContainer>

        <GuiContainer mt-30>
            <GuiButton @click="save" :color="'green'">Сохранить</GuiButton>
            <GuiButton @click="cancel">Отмена</GuiButton>
        </GuiContainer>

    </LayoutPage>



</template>

<script>
import form from "@/Core/Form";
import LayoutPage from "@/Components/Layout/LayoutPage";
import GuiContainer from "@/Components/GUI/GuiContainer";
import GuiButton from "@/Components/GUI/GuiButton";
import FormString from "@/Components/Form/FormString";
import FormDictionary from "@/Components/Form/FormDictionary";

export default {
    components: {
        FormDictionary,
        FormString,
        GuiButton,
        GuiContainer,
        LayoutPage,
    },

    data: () => ({
        form: form('/api/piers/get', '/api/piers/update'),
    }),

    computed: {
        pierId() {
            return Number(this.$route.params.id);
        },
        processing() {
            return this.form.is_loading || this.form.is_saving;
        },
    },

    created() {
        this.form.toaster = this.$toast;
        this.form.load({id: this.pierId});
    },

    methods: {
        save() {
            if (!this.form.validate()) {
                return;
            }
            this.form.save({id: this.pierId})
            .then(response => {
                if (this.pierId === 0) {
                    this.$router.push({name: 'pier-view', params: {id: response.payload['id']}});
                } else {
                    this.$router.push({name: 'pier-view', params: {id: this.pierId}});
                }
            })
        },
        cancel() {
            if (this.pierId === 0) {
                this.$router.push({name: 'pier-list'});
            } else {
                this.$router.push({name: 'pier-view', params: {id: this.pierId}})
            }
        },
    }
}
</script>
