<template>
    <LayoutPage :loading="processing" :title="form.payload['name']"
                :breadcrumbs="[{caption: 'Сотрудники', to: {name: 'staff-list'}}]"
                :link="{name: 'staff-list'}"
                :link-title="'К списку сотрудников'"
    >
        <GuiContainer mt-30>
            <FormString :form="form" :name="'last_name'"/>
            <FormString :form="form" :name="'first_name'"/>
            <FormString :form="form" :name="'patronymic'"/>
            <FormDictionary :form="form" :name="'status_id'" :dictionary="'position_statuses'"/>
            <FormString :form="form" :name="'position_title'"/>
            <FormString :form="form" :name="'external_id'"/>
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
            <FormPhone :form="form" :name="'mobile_phone'" :mask="'+7 (###) ###-##-##'"/>
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

export default {
    components: {
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
        form: form('/api/staff/get', '/api/staff/update'),
    }),

    computed: {
        staffId() {
            return Number(this.$route.params.id);
        },
        processing() {
            return this.form.is_loading || this.form.is_saving;
        },
    },

    created() {
        this.form.toaster = this.$toast;
        this.form.load({id: this.staffId});
    },

    methods: {
        save() {
            if (!this.form.validate()) {
                return;
            }
            this.form.save({id: this.staffId})
                .then(response => {
                    if (this.staffId === 0) {
                        this.$router.push({name: 'staff-view', params: {id: response.payload['id']}});
                    } else {
                        this.$router.push({name: 'staff-view', params: {id: this.staffId}});
                    }
                })
        },
        cancel() {
            if (this.staffId === 0) {
                this.$router.push({name: 'staff-list'});
            } else {
                this.$router.push({name: 'staff-view', params: {id: this.staffId}});
            }
        },
    }
}
</script>
