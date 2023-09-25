<template>
    <LayoutPage :loading="processing" :title="form.payload['title']"
                :breadcrumbs="[{caption: 'Промоутеры', to: {name: 'promoters-list'}}]"
                :link="{name: 'promoters-list'}"
                :link-title="'К списку промоутеров'"
    >
        <GuiContainer mt-30>
            <FormString :form="form" :name="'last_name'" :autocomplete="'nope'"/>
            <FormString :form="form" :name="'first_name'" :autocomplete="'nope'"/>
            <FormString :form="form" :name="'patronymic'" :autocomplete="'nope'"/>
            <FormString :form="form" :name="'email'"/>
            <FormPhone :form="form" :name="'phone'"/>
        </GuiContainer>

        <GuiContainer mt-30>
            <FormText :form="form" :name="'notes'"/>
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
import FormString from "@/Components/Form/FormString";
import FormDictionary from "@/Components/Form/FormDictionary";
import FormNumber from "@/Components/Form/FormNumber";
import FormDropdown from "@/Components/Form/FormDropdown";
import GuiButton from "@/Components/GUI/GuiButton";
import FormText from "@/Components/Form/FormText";
import FormFiles from "@/Components/Form/FormFiles";
import form from "@/Core/Form";
import FormPhone from "@/Components/Form/FormPhone.vue";

export default {
    components: {
        FormPhone,
        FormFiles,
        FormText,
        GuiButton,
        FormDropdown,
        FormNumber,
        FormDictionary,
        FormString,
        GuiContainer,
        LayoutPage

    },

    data: () => ({
        form: form('/api/promoters/get', '/api/promoters/update'),
    }),

    computed: {
        partnerId() {
            return Number(this.$route.params.id);
        },
        processing() {
            return this.form.is_loading || this.form.is_saving;
        },
    },

    created() {
        this.form.toaster = this.$toast;
        this.form.load({id: this.partnerId});
    },

    methods: {
        save() {
            if (!this.form.validate()) {
                return;
            }
            this.form.save({id: this.partnerId})
                .then(response => {
                    if (this.partnerId === 0) {
                        this.$router.push({name: 'promoters-view', params: {id: response.payload['id']}});
                    } else {
                        this.$router.push({name: 'promoters-view', params: {id: this.partnerId}});
                    }
                })
        },
        cancel() {
            if (this.partnerId === 0) {
                this.$router.push({name: 'promoters-list'});
            } else {
                this.$router.push({name: 'promoters-view', params: {id: this.partnerId}})
            }
        },
    }
}
</script>
