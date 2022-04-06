<template>
    <LayoutPage :loading="processing" :title="form.payload['title']"
                :breadcrumbs="[{caption: 'Представители', to: {name: 'representatives-list'}}]"
                :link="{name: 'representatives-list'}"
                :link-title="'К списку представителей'"
    >
        <GuiContainer mt-30>
            <FormString :form="form" :name="'last_name'" :autocomplete="'nope'"/>
            <FormString :form="form" :name="'first_name'" :autocomplete="'nope'"/>
            <FormString :form="form" :name="'patronymic'" :autocomplete="'nope'"/>
            <FormString :form="form" :name="'default_position_title'"/>
        </GuiContainer>
        <GuiContainer mt-30>
            <FormDate :form="form" :name="'birthdate'"/>
            <FormDropdown :form="form" :name="'gender'" :identifier="'id'" :show="'name'" :options="[
                {id: 'male', name: 'Мужской'},
                {id: 'female', name: 'Женский'},
            ]" :placeholder="'Выберите пол'"/>
        </GuiContainer>
        <GuiContainer mt-30>
            <FormString :form="form" :name="'email'"/>
            <GuiContainer>
                <GuiContainer w-50 inline-flex>
                    <FormPhone :form="form" :name="'work_phone'"/>
                </GuiContainer>
                <GuiContainer w-50 pl-20 inline-flex>
                    <FormString :form="form" :name="'work_phone_additional'"/>
                </GuiContainer>
            </GuiContainer>
            <FormPhone :form="form" :name="'mobile_phone'"/>
            <FormString :form="form" :name="'vkontakte'"/>
            <FormString :form="form" :name="'facebook'"/>
            <FormString :form="form" :name="'telegram'"/>
            <FormString :form="form" :name="'skype'"/>
            <FormString :form="form" :name="'whatsapp'"/>
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
import form from "@/Core/Form";
import LayoutPage from "@/Components/Layout/LayoutPage";
import GuiContainer from "@/Components/GUI/GuiContainer";
import FormString from "@/Components/Form/FormString";
import GuiButton from "@/Components/GUI/GuiButton";
import FormText from "@/Components/Form/FormText";
import FormPhone from "@/Components/Form/FormPhone";
import FormDropdown from "@/Components/Form/FormDropdown";
import FormDate from "@/Components/Form/FormDate";

export default {
    components: {
        FormDate,
        FormDropdown,
        FormPhone,
        FormText,
        GuiButton,
        FormString,
        GuiContainer,
        LayoutPage,

    },

    data: () => ({
        form: form('/api/representatives/get', '/api/representatives/update'),
    }),

    computed: {
        representativeId() {
            return Number(this.$route.params.id);
        },
        processing() {
            return this.form.is_loading || this.form.is_saving;
        },
    },

    created() {
        this.form.toaster = this.$toast;
        this.form.load({id: this.representativeId});
    },

    methods: {
        save() {
            if (!this.form.validate()) {
                return;
            }
            this.form.save({id: this.representativeId})
                .then(response => {
                    if (this.representativeId === 0) {
                        this.$router.push({name: 'representatives-view', params: {id: response.payload['id']}});
                    } else {
                        this.$router.push({name: 'representatives-view', params: {id: this.representativeId}});
                    }
                })
        },

        cancel() {
            if (this.representativeId === 0) {
                this.$router.push({name: 'representatives-list'});
            } else {
                this.$router.push({name: 'representatives-view', params: {id: this.representativeId}});
            }
        },
    }
}
</script>
