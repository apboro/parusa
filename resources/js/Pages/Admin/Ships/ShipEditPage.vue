<template>
    <LayoutPage :loading="processing" :title="form.payload['name']"
                :breadcrumbs="[{caption: 'Теплоходы', to: {name: 'ship-list'}}]"
                :link="{name: 'ship-list'}"
                :link-title="'К списку теплоходов'"
    >
        <GuiContainer mt-30>
            <FormString :form="form" :name="'name'"/>
            <FormString :form="form" :name="'capacity'"/>
            <FormDictionary :form="form" :name="'status_id'" :dictionary="'ships_statuses'"/>
            <FormCheckBox :form="form" :name="'ship_has_seats_scheme'" :hide-title="true"/>
        </GuiContainer>

        <GuiContainer mt-30>
            <FormText :form="form" :name="'description'"/>
        </GuiContainer>

        <GuiContainer mt-30>
            <GuiButton @click="save" :color="'green'">Сохранить</GuiButton>
            <GuiButton @click="cancel">Отмена</GuiButton>
        </GuiContainer>
    </LayoutPage>
</template>

<script>

import LayoutPage from "@/Components/Layout/LayoutPage";
import GuiContainer from "@/Components/GUI/GuiContainer";
import form from "@/Core/Form";
import FormString from "@/Components/Form/FormString";
import FormDictionary from "@/Components/Form/FormDictionary";
import FormDate from "@/Components/Form/FormDate";
import FormDropdown from "@/Components/Form/FormDropdown";
import FormPhone from "@/Components/Form/FormPhone";
import FormText from "@/Components/Form/FormText";
import GuiButton from "@/Components/GUI/GuiButton";
import FormCheckBox from "@/Components/Form/FormCheckBox.vue";

export default {
    components: {
        FormCheckBox,
        GuiButton,
        FormText,
        FormPhone,
        FormDropdown,
        FormDate,
        FormDictionary,
        FormString,
        GuiContainer,
        LayoutPage

    },

    data: () => ({
        form: form('/api/ship/get', '/api/ship/update'),
    }),

    computed: {
        shipId() {
            return Number(this.$route.params.id);
        },
        processing() {
            return this.form.is_loading || this.form.is_saving;
        },
    },

    created() {
        this.form.toaster = this.$toast;
        this.form.load({id: this.shipId});
    },

    methods: {
        save() {
            if (!this.form.validate()) {
                return;
            }
            this.form.save({id: this.shipId})
                .then(response => {
                    if (this.shipId === 0) {
                        this.$router.push({name: 'ship-view', params: {id: response.payload['id']}});
                    } else {
                        this.$router.push({name: 'ship-view', params: {id: this.shipId}});
                    }
                })
        },
        cancel() {
            if (this.shipId === 0) {
                this.$router.push({name: 'ship-list'});
            } else {
                this.$router.push({name: 'ship-view', params: {id: this.shipId}});
            }
        },
    }
}
</script>
