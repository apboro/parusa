<template>
    <LayoutPage :loading="processing" :title="form.payload['name']"
                :breadcrumbs="[{caption: 'Причалы', to: {name: 'pier-list'}}]"
                :link="{name: 'pier-list'}"
                :link-title="'К списку причалов'"
    >
        <GuiContainer mt-30>
            <FormString :form="form" :name="'name'"/>
            <FormDictionary :form="form" :dictionary="'pier_statuses'" :name="'status_id'" :fresh="true"/>
            <FormImages :form="form" :name="'images'"/>
            <FormString :form="form" :name="'work_time'"/>
            <FormPhone :form="form" :name="'phone'"/>
            <FormString :form="form" :name="'address'"/>
            <FormString :form="form" :name="'latitude'"/>
            <FormString :form="form" :name="'longitude'"/>
            <FormText :form="form" :name="'description'"/>
            <FormText :form="form" :name="'way_to'"/>
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
import FormString from "@/Components/Form/FormString";
import FormDictionary from "@/Components/Form/FormDictionary";
import FormText from "@/Components/Form/FormText";
import FormImages from "@/Components/Form/FormImages";
import GuiButton from "@/Components/GUI/GuiButton";
import FormPhone from "@/Components/Form/FormPhone";

export default {
    components: {
        FormPhone,
        LayoutPage,
        GuiContainer,
        FormString,
        FormDictionary,
        FormImages,
        FormText,
        GuiButton,
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
