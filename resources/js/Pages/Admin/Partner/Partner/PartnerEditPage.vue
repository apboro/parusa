<template>
    <page :loading="processing">
        <template v-slot:header>
            <page-title-bar
                :title="form.payload['title']"
                :title-link="partnerId === 0 ? null : backLink"
                :breadcrumbs="[{caption: 'Партнёры', to: {name: 'partners-list'}}]"
                :link="{name: 'partners-list'}"
                :link-title="'К списку партнёров'"
            />
        </template>

        <container mt-30>
            <data-field-input :datasource="form" :name="'name'"/>
            <data-field-dictionary-dropdown :datasource="form" :dictionary="'partner_types'" :name="'type_id'"/>
            <data-field-dictionary-dropdown :datasource="form" :dictionary="'position_statuses'" :name="'status_id'"/>
            <data-field-input :datasource="form" :name="'tickets_for_guides'" :type="'number'"/>
            <data-field-dropdown :datasource="form" :name="'can_reserve_tickets'" :key-by="'id'" :value-by="'name'" :options="[
                {id: 0, name: 'Запрещено'},
                {id: 1, name: 'Разрешено'},
            ]" :placeholder="'Бронирование билетов'"/>
        </container>

        <container mt-30>
            <data-field-files :datasource="form" :name="'documents'"/>
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
import Container from "../../../../Components/GUI/GuiContainer";
import DataFieldInput from "../../../../Components/DataFields/DataFieldInput";
import DataFieldDictionaryDropdown from "../../../../Components/DataFields/DataFieldDictionaryDropdown";
import DataFieldDropdown from "../../../../Components/DataFields/DataFieldDropdown";
import DataFieldTextArea from "../../../../Components/DataFields/DataFieldTextArea";
import BaseButton from "../../../../Components/GUI/GuiButton";
import DataFieldFiles from "../../../../Components/DataFields/DataFieldFiles";

export default {
    components: {
        DataFieldFiles,
        Page,
        PageTitleBar,
        Container,
        DataFieldInput,
        DataFieldDictionaryDropdown,
        DataFieldDropdown,
        DataFieldTextArea,
        BaseButton,
    },

    data: () => ({
        form: null,
    }),

    computed: {
        partnerId() {
            return Number(this.$route.params.id);
        },
        processing() {
            return this.form.loading || this.form.saving;
        },
        backLink() {
            return this.partnerId === 0 ? {name: 'partners-list'} : {name: 'partners-view', params: {id: this.partnerId}}
        },
    },

    created() {
        this.form = formDataSource('/api/partners/get', '/api/partners/update', {id: this.partnerId});
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
            if (this.partnerId === 0) {
                const newId = payload['id'];
                this.$router.push({name: 'partners-view', params: {id: newId}});
            } else {
                this.$router.push({name: 'partners-view', params: {id: this.partnerId}});
            }
        },
    }
}
</script>
