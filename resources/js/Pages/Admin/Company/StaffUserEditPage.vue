<template>
    <page>
        <template v-slot:header>{{ $route.meta.title }}</template>

        <loading-progress :loading="form.loading">
            <base-input name="'last_name'"
                        v-model="form.values['last_name']"
                        :original="form.originals['last_name']"
                        @changed="setValue"
            />
            <span v-if="form.validation_errors['last_name']">{{ form.validation_errors['last_name'].join(', ') }}</span>
            <base-input name="'first_name'"
                        v-model="form.values['first_name']"
                        :original="form.originals['first_name']"
                        @changed="setValue"
            />
            <span v-if="form.validation_errors['first_name']">{{
                    form.validation_errors['first_name'].join(', ')
                }}</span>
            <base-input name="'patronymic'"
                        v-model="form.values['patronymic']"
                        :original="form.originals['patronymic']"
                        @changed="setValue"
            />
            <span v-if="form.validation_errors['patronymic']">{{
                    form.validation_errors['patronymic'].join(', ')
                }}</span>
            <base-input name="'position_title'"
                        v-model="form.values['position_title']"
                        :original="form.originals['position_title']"
                        @changed="setValue"
            />
            <span v-if="form.validation_errors['position_title']">{{
                    form.validation_errors['position_title'].join(', ')
                }}</span>
            <base-input name="'position_status_id'"
                        v-model="form.values['position_status_id']"
                        :original="form.originals['position_status_id']"
                        @changed="setValue"
            />
            <span v-if="form.validation_errors['position_status_id']">{{
                    form.validation_errors['position_status_id'].join(', ')
                }}</span>
            <base-input name="'birth_date'"
                        v-model="form.values['birth_date']"
                        :original="form.originals['birth_date']"
                        @changed="setValue"
            />
            <span v-if="form.validation_errors['birth_date']">{{
                    form.validation_errors['birth_date'].join(', ')
                }}</span>
        </loading-progress>

    </page>
</template>

<script>
import formDataSource from "../../../Helpers/Core/formDataSource";

import Page from "../../../Layouts/Page";
import LoadingProgress from "../../../Components/LoadingProgress";

import BaseInput from "../../../Components/Base/BaseInput";

export default {
    components: {
        Page,
        LoadingProgress,
        BaseInput,
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
