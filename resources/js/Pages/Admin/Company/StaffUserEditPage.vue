<template>
    <page :loading="form.loading">
        <template v-slot:header>
            <page-title-bar :title="form.payload['full_name']" :breadcrumbs="[
                {caption: 'Сотрудники', to: {name: 'staff-user-list'}},
            ]"/>
        </template>

        <container>
            <data-field-input :datasource="form" :name="'last_name'"/>
            <data-field-input :datasource="form" :name="'first_name'"/>
            <data-field-input :datasource="form" :name="'patronymic'"/>
            <data-field-input :datasource="form" :name="'position_title'"/>
            <data-field-dictionary-dropdown :datasource="form" :dictionary="'position_statuses'"
                                            :name="'position_status_id'"/>
            <data-field-input :datasource="form" :name="'birth_date'"/>
        </container>

        <container :no-bottom="true">
            <base-button @click="toast" :type="'green'">Сохранить</base-button>
            <base-button @click="$router.push({ name: 'staff-user-view', params: { id: this.userId }})">Отмена
            </base-button>
        </container>

    </page>
</template>

<script>
import formDataSource from "../../../Helpers/Core/formDataSource";

import Page from "../../../Layouts/Page";
import LoadingProgress from "../../../Components/LoadingProgress";
import DataFieldInput from "../../../Components/DataFields/DataFieldInput";
import DataFieldDictionaryDropdown from "../../../Components/DataFields/DataFieldDictionaryDropdown";
import Container from "../../../Layouts/Parts/Container";
import BaseButton from "../../../Components/Base/BaseButton";
import BaseLinkButton from "../../../Components/Base/BaseLinkButton";
import PageTitleBar from "../../../Layouts/Parts/PageTitleBar";

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
        this.form = formDataSource('/api/users/staff/edit', '/api/users/staff/save', {id: this.userId});
        this.form.load();
    },

    methods: {
        toast() {
            this.$toast.success('Success Test Message', 0);
        },
    }
}
</script>
