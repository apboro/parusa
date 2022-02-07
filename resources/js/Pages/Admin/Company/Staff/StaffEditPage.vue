<template>
    <page :loading="processing">
        <template v-slot:header>
            <page-title-bar
                :title="form.payload['title']"
                :title-link="staffId === 0 ? null : backLink"
                :breadcrumbs="[{caption: 'Сотрудники', to: {name: 'staff-list'}}]"
                :link="{name: 'staff-list'}"
                :link-title="'К списку сотрудников'"
            />
        </template>

        <container mt-30>
            <data-field-input :datasource="form" :name="'last_name'"/>
            <data-field-input :datasource="form" :name="'first_name'"/>
            <data-field-input :datasource="form" :name="'patronymic'"/>
            <data-field-dictionary-dropdown :datasource="form" :dictionary="'position_statuses'" :name="'status_id'"/>
            <data-field-input :datasource="form" :name="'position_title'"/>
        </container>
        <container mt-30>
            <data-field-date :datasource="form" :name="'birthdate'"/>
            <data-field-dropdown :datasource="form" :name="'gender'" :key-by="'id'" :value-by="'name'" :options="[
                {id: 'male', name: 'Мужской'},
                {id: 'female', name: 'Женский'},
            ]" :placeholder="'Выберите пол'"/>
        </container>
        <container mt-30>
            <data-field-input :datasource="form" :name="'email'"/>
            <data-field-masked-input :class="'w-50 inline-flex'" :datasource="form" :name="'work_phone'" :mask="'+7 (###) ###-##-##'"/>
            <data-field-input :class="'w-50 pl-20 inline-flex'" :datasource="form" :name="'work_phone_additional'"/>
            <data-field-masked-input :datasource="form" :name="'mobile_phone'" :mask="'+7 (###) ###-##-##'"/>
            <data-field-input :datasource="form" :name="'vkontakte'"/>
            <data-field-input :datasource="form" :name="'facebook'"/>
            <data-field-input :datasource="form" :name="'telegram'"/>
            <data-field-input :datasource="form" :name="'skype'"/>
            <data-field-input :datasource="form" :name="'whatsapp'"/>
        </container>

        <container mt-30>
            <data-field-text-area :datasource="form" :name="'notes'"/>
        </container>

        <container mt-30>
            <base-button @click="save" :color="'green'">Сохранить</base-button>
            <base-button @click="$router.push(backLink)">Отмена
            </base-button>
        </container>

    </page>
</template>

<script>
import formDataSource from "../../../../Helpers/Core/formDataSource";

import Page from "../../../../Layouts/Page";
import PageTitleBar from "../../../../Layouts/Parts/PageTitleBar";
import Container from "../../../../Components/GUI/GuiContainer";
import DataFieldInput from "../../../../Components/DataFields/DataFieldInput";
import DataFieldDictionaryDropdown from "../../../../Components/DataFields/DataFieldDictionaryDropdown";
import DataFieldMaskedInput from "../../../../Components/DataFields/DataFieldMaskedInput";
import DataFieldTextArea from "../../../../Components/DataFields/DataFieldTextArea";
import DataFieldDropdown from "../../../../Components/DataFields/DataFieldDropdown";
import BaseButton from "../../../../Components/GUI/GuiButton";
import DataFieldDate from "../../../../Components/DataFields/DataFieldDate";

export default {
    components: {
        DataFieldDate,
        Page,
        PageTitleBar,
        Container,
        DataFieldInput,
        DataFieldDropdown,
        DataFieldDictionaryDropdown,
        DataFieldMaskedInput,
        DataFieldTextArea,
        BaseButton,
    },

    data: () => ({
        form: null,
    }),

    computed: {
        staffId() {
            return Number(this.$route.params.id);
        },
        processing() {
            return this.form.loading || this.form.saving;
        },
        backLink() {
            return this.staffId === 0 ? {name: 'staff-list'} : {name: 'staff-view', params: {id: this.staffId}}
        },
    },

    created() {
        this.form = formDataSource('/api/company/staff/get', '/api/company/staff/update', {id: this.staffId});
        this.form.toaster = this.$toast;
        this.form.afterSave = this.afterSave;
        this.form.load();
    },

    methods: {
        save() {
            if (!this.form.validateAll()) {
                return;
            }
            this.form.save()
        },
        afterSave(payload) {
            if (this.staffId === 0) {
                const newId = payload['id'];
                this.$router.push({name: 'staff-view', params: {id: newId}});
            } else {
                this.$router.push({name: 'staff-view', params: {id: this.staffId}});
            }
        },
    }
}
</script>
