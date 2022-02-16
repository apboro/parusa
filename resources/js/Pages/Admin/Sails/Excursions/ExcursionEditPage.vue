<template>
    <page :loading="processing">
        <template v-slot:header>
            <page-title-bar
                :title="form.payload['title']"
                :title-link="excursionId === 0 ? null : backLink"
                :breadcrumbs="[{caption: 'Каталог экскурсий', to: {name: 'excursion-list'}}]"
                :link="{name: 'excursion-list'}"
                :link-title="'К списку экскурсий'"
            />
        </template>

        <container mt-30>
            <data-field-input :datasource="form" :name="'name'"/>
            <data-field-dictionary-dropdown :datasource="form" :dictionary="'excursion_statuses'" :name="'status_id'"/>
            <data-field-images :datasource="form" :name="'images'"/>
            <data-field-dictionary-dropdown-multi :datasource="form" :dictionary="'excursion_programs'" :name="'programs'"/>
            <data-field-input :datasource="form" :name="'duration'" :type="'number'"/>
            <data-field-text-area :datasource="form" :name="'announce'"/>
            <data-field-text-area :datasource="form" :name="'description'"/>
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
import Container from "../../../../Components/GUI/GuiContainer";
import DataFieldInput from "../../../../Components/DataFields/DataFieldInput";
import DataFieldDictionaryDropdown from "../../../../Components/DataFields/DataFieldDictionaryDropdown";
import DataFieldImages from "../../../../Components/DataFields/DataFieldImages";
import DataFieldDictionaryDropdownMulti from "../../../../Components/DataFields/DataFieldDictionaryDropdownMulti";
import DataFieldTextArea from "../../../../Components/DataFields/DataFieldTextArea";
import BaseButton from "../../../../Components/GUI/GuiButton";

export default {
    components: {
        Page,
        PageTitleBar,
        Container,
        DataFieldInput,
        DataFieldDictionaryDropdown,
        DataFieldImages,
        DataFieldDictionaryDropdownMulti,
        DataFieldTextArea,
        BaseButton,
    },

    data: () => ({
        form: null,
    }),

    computed: {
        excursionId() {
            return Number(this.$route.params.id);
        },
        processing() {
            return this.form.loading || this.form.saving;
        },
        backLink() {
            return this.excursionId === 0 ? {name: 'excursion-list'} : { name: 'excursion-view', params: { id: this.excursionId }}
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
            if (!this.form.validateAll()) {
                return;
            }
            this.form.save()
        },
        afterSave(payload) {
            if (Number(this.excursionId) === 0) {
                const newId = payload['id'];
                this.$router.push({name: 'excursion-view', params: {id: newId}});
            } else {
                this.$router.push({name: 'excursion-view', params: {id: this.excursionId}});
            }
        },
    }
}
</script>
