<template>
    <page :loading="form.loading">
        <template v-slot:header>
            <page-title-bar :title="form.payload['title']" :breadcrumbs="[
                {caption: 'Сотрудники', to: {name: 'staff-user-list'}},
            ]"/>
        </template>

        <container>
            <data-field-input :datasource="form" :name="'lastname'"/>
            <data-field-input :datasource="form" :name="'firstname'"/>
            <data-field-input :datasource="form" :name="'patronymic'"/>
            <data-field-input :datasource="form" :name="'position_title'"/>
            <data-field-dictionary-dropdown :datasource="form" :dictionary="'position_statuses'"
                                            :name="'position_status_id'"/>
            <data-field-input :datasource="form" :name="'birthdate'"/>
        </container>

        <container :no-bottom="true">
            <base-button @click="save" :color="'green'" :disabled="!form.valid_all">Сохранить</base-button>
            <base-button @click="$router.push({ name: 'staff-user-view', params: { id: this.userId }})">Отмена
            </base-button>
        </container>

    </page>
</template>

<script>
import formDataSource from "../../../../Helpers/Core/formDataSource";

import Page from "../../../../Layouts/Page";
import LoadingProgress from "../../../../Components/LoadingProgress";
import DataFieldInput from "../../../../Components/DataFields/DataFieldInput";
import DataFieldDictionaryDropdown from "../../../../Components/DataFields/DataFieldDictionaryDropdown";
import Container from "../../../../Layouts/Parts/Container";
import BaseButton from "../../../../Components/Base/BaseButton";
import BaseLinkButton from "../../../../Components/Base/BaseLinkButton";
import PageTitleBar from "../../../../Layouts/Parts/PageTitleBar";

export default {
    components: {
        PageTitleBar,
        Page,
        LoadingProgress,
        DataFieldInput,
        DataFieldDictionaryDropdown,
        Container,
        BaseButton,
        BaseLinkButton,
    },

    data: () => ({
        form: null,
    }),

    computed: {
        userId() {
            return this.$route.params.id;
        }
    },

    created() {
        this.form = formDataSource('/api/users/staff/get', '/api/users/staff/update', {id: this.userId});
        this.form.toaster = this.$toast;
        this.form.afterSave = this.afterSave;
        this.form.load();
    },

    methods: {
        save() {
            if (!this.form.valid_all) {
                return;
            }
            this.form.save()
        },
        afterSave(payload) {
            if (Number(this.userId) === 0) {
                const newId = payload['id'];
                this.$router.push({name: 'staff-user-edit', params: {id: newId}});
            }
        },
    }
}
</script>
