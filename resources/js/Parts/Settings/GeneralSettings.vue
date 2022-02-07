<template>
    <loading-progress :loading="processing">
        <container w-100 mt-30>
            <data-field-input :class="'field__w50'" :datasource="form" :name="'default_cancellation_time'" :type="'number'"/>
            <data-field-input :class="'field__w50'" :datasource="form" :name="'cancellation_notify_time'" :type="'number'"/>
            <data-field-dropdown :class="'field__w50'" :datasource="form" :name="'tickets_limit_by_capacity'" :key-by="'id'" :value-by="'name'" :options="[
                {id: 0, name: 'Нет'},
                {id: 1, name: 'Да'},
            ]"/>
            <data-field-text-area :class="'field__col'" :datasource="form" :name="'buyer_email_welcome'"/>
        </container>

        <container mt-30>
            <base-button @click="save" :color="'green'">Сохранить</base-button>
        </container>
    </loading-progress>
</template>

<script>
import formDataSource from "../../Helpers/Core/formDataSource";
import Container from "../../Components/GUI/GuiContainer";
import BaseButton from "../../Components/GUI/GuiButton";
import LoadingProgress from "../../Components/LoadingProgress";
import DataFieldInput from "../../Components/DataFields/DataFieldInput";
import DataFieldDropdown from "../../Components/DataFields/DataFieldDropdown";
import DataFieldTextArea from "../../Components/DataFields/DataFieldTextArea";

export default {
    components: {
        DataFieldTextArea,
        DataFieldDropdown,
        DataFieldInput,
        LoadingProgress,
        Container,
        BaseButton,
    },

    data: () => ({
        form: null,
    }),

    computed: {
        processing() {
            return this.form.loading || this.form.saving;
        },
    },

    created() {
        this.form = formDataSource('/api/settings/general/get', '/api/settings/general/set');
        this.form.toaster = this.$toast;
        this.form.load();
    },

    methods: {
        save() {
            if (!this.form.validateAll()) {
                return;
            }
            this.form.save()
        },
    }
}
</script>

