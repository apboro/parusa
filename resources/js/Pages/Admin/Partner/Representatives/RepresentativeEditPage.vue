<template>
    <page :loading="form.loading">
        <template v-slot:header>
            <page-title-bar
                :title="form.payload['title']"
                :title-link="representativeId === 0 ? null : backLink"
                :breadcrumbs="[{caption: 'Представители', to: {name: 'representatives-list'}}]"
                :link="{name: 'representatives-list'}"
                :link-title="'К списку представителей'"
            />
        </template>

        <container mt-30>
            <data-field-input :datasource="form" :name="'last_name'"/>
            <data-field-input :datasource="form" :name="'first_name'"/>
            <data-field-input :datasource="form" :name="'patronymic'"/>
            <data-field-input :datasource="form" :name="'default_position_title'"/>
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
            <base-button @click="$router.push(backLink)">Отмена</base-button>
        </container>

    </page>
</template>

<script>
import formDataSource from "../../../../Helpers/Core/formDataSource";

import Page from "../../../../Layouts/Page";
import PageTitleBar from "../../../../Layouts/Parts/PageTitleBar";
import Container from "../../../../Components/GUI/Container";
import DataFieldInput from "../../../../Components/DataFields/DataFieldInput";
import DataFieldDropdown from "../../../../Components/DataFields/DataFieldDropdown";
import DataFieldMaskedInput from "../../../../Components/DataFields/DataFieldMaskedInput";
import DataFieldTextArea from "../../../../Components/DataFields/DataFieldTextArea";
import BaseButton from "../../../../Components/Base/BaseButton";
import DataFieldDate from "../../../../Components/DataFields/DataFieldDate";

export default {
    components: {
        DataFieldDate,
        Page,
        PageTitleBar,
        Container,
        DataFieldInput,
        DataFieldDropdown,
        DataFieldMaskedInput,
        DataFieldTextArea,
        BaseButton,
    },

    data: () => ({
        form: null,
    }),

    computed: {
        representativeId() {
            return Number(this.$route.params.id);
        },
        processing() {
            return this.form.loading || this.form.saving;
        },
        backLink() {
            return this.representativeId === 0 ? {name: 'representatives-list'} : {name: 'representatives-view', params: {id: this.representativeId}}
        },
    },

    created() {
        this.form = formDataSource('/api/representatives/get', '/api/representatives/update', {id: this.representativeId});
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
            if (this.representativeId === 0) {
                const newId = payload['id'];
                this.$router.push({name: 'representatives-view', params: {id: newId}});
            } else {
                this.$router.push({name: 'representatives-view', params: {id: this.representativeId}});
            }
        },
    }
}
</script>
