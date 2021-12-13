<template>
    <page :loading="processing">
        <template v-slot:header>
            <page-title-bar :title="form.payload['title']" :breadcrumbs="[
                {caption: 'Каталог экскурсий', to: {name: 'excursion-list'}},
            ]"/>
        </template>

        <container>
            <data-field-input :datasource="form" :name="'name'"/>
            <data-field-dictionary-dropdown :datasource="form" :dictionary="'excursion_statuses'" :name="'status_id'"/>
        </container>

        <container :no-bottom="true">
            <base-button @click="save" :color="'green'" :disabled="!form.valid_all">Сохранить</base-button>
            <base-button @click="$router.push({ name: 'excursion-view', params: { id: this.excursionId }})">Отмена
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
        excursionId() {
            return this.$route.params.id;
        },
        processing() {
            return this.form.loading || this.form.saving;
        },
    },

    created() {
        this.form = formDataSource('/api/excursions/get', '/api/excursions/update', {id: this.excursionId});
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
                this.$router.push({name: 'excursion-edit', params: {id: newId}});
            }
        },
    }
}
</script>
