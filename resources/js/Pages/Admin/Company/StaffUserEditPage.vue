<template>
    <page :loading="form.loading">
        <template v-slot:header>{{ $route.meta.title }}</template>

            <container>
                <data-field-input :datasource="form" :name="'last_name'"/>
                <data-field-input :datasource="form" :name="'first_name'"/>
                <data-field-input :datasource="form" :name="'patronymic'"/>
                <data-field-input :datasource="form" :name="'position_title'"/>
                <data-field-dictionary-dropdown :datasource="form" :dictionary="'position_statuses'" :name="'position_status_id'"/>
                <data-field-input :datasource="form" :name="'birth_date'"/>
            </container>

            <container>
                <base-button>Сохранить</base-button>
                <base-link-button :to="{ name: 'staff-user-view', params: { id: userId }}" :type="'gray'">Отмена</base-link-button>
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

export default {
    components: {
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
        setValue() {
            this.form.validateAll();
        }
    }
}
</script>
