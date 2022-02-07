<template>
    <page :loading="processing">
        <template v-slot:header>
            <page-title-bar :title="form.payload['title']"
                            :breadcrumbs="[{caption: 'Причалы', to: {name: 'pier-list'}}]"
            />
        </template>

        <container mt-30>
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

        <container mt-30>
            <base-button @click="save" :color="'green'">Сохранить</base-button>
            <base-button @click="$router.push({ name: 'pier-view', params: { id: this.pierId }})">Отмена</base-button>
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
        DataFieldTextArea,
        BaseButton,
    },

    data: () => ({
        form: null,
    }),

    computed: {
        pierId() {
            return Number(this.$route.params.id);
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
            if (!this.form.validateAll()) {
                return;
            }
            this.form.save()
        },
        afterSave(payload) {
            if (this.pierId === 0) {
                const newId = payload['id'];
                this.$router.push({name: 'pier-view', params: {id: newId}});
            } else {
                this.$router.push({name: 'pier-view', params: {id: this.pierId}});
            }
        },
    }
}
</script>
