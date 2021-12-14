<template>
    <page :loading="processing">
        <template v-slot:header>
            <page-title-bar :title="form.payload['title']" :breadcrumbs="[
                {caption: 'Причалы', to: {name: 'pier-list'}},
            ]"/>
        </template>

        <container>
            <data-field-input :datasource="form" :name="'name'"/>
            <data-field-dictionary-dropdown :datasource="form" :dictionary="'pier_statuses'" :name="'status_id'"/>
            <data-field-images :datasource="form" :name="'images'"/>
            <data-field-input :datasource="form" :name="'work_time'"/>
            <data-field-input :datasource="form" :name="'address'"/>
            <data-field-input :datasource="form" :name="'latitude'"/>
            <data-field-input :datasource="form" :name="'longitude'"/>
            <data-field-text-area :datasource="form" :name="'description'"/>
            <data-field-text-area :datasource="form" :name="'way_to'"/>
        </container>

        <container :no-bottom="true">
            <base-button @click="save" :color="'green'" :disabled="!form.valid_all">Сохранить</base-button>
            <base-button @click="$router.push({ name: 'pier-view', params: { id: this.pierId }})">Отмена
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
import DataFieldTextArea from "../../../../Components/DataFields/DataFieldTextArea";
import DataFieldImages from "../../../../Components/DataFields/DataFieldImages";

export default {
    components: {
        DataFieldImages,
        DataFieldTextArea,
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
        pierId() {
            return this.$route.params.id;
        },
        processing() {
            return this.form.loading || this.form.saving;
        },
    },

    created() {
        this.form = formDataSource('/api/piers/get', '/api/piers/update', {id: this.pierId});
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
                this.$router.push({name: 'pier-edit', params: {id: newId}});
            }
        },
    }
}
</script>
