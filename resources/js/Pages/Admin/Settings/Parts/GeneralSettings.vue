<template>
    <LoadingProgress :loading="processing">
        <GuiContainer w-100 mt-30>
            <FormNumber :class="'settings-field__50'" :form="form" :name="'default_cancellation_time'"/>
            <FormNumber :class="'settings-field__50'" :form="form" :name="'cancellation_notify_time'"/>
            <FormNumber :class="'settings-field__50'" :form="form" :name="'promoters_commission_integrated_excursions'"/>
            <FormText :class="'settings-field__col'" :form="form" :name="'buyer_email_welcome'"/>
        </GuiContainer>

        <GuiContainer mt-30>
            <GuiButton @click="save" :color="'green'">Сохранить</GuiButton>
        </GuiContainer>
    </LoadingProgress>
</template>

<script>
import LoadingProgress from "@/Components/LoadingProgress";
import GuiContainer from "@/Components/GUI/GuiContainer";
import form from "@/Core/Form";
import FormNumber from "@/Components/Form/FormNumber";
import FormText from "@/Components/Form/FormText";
import GuiButton from "@/Components/GUI/GuiButton";

export default {
    components: {
        GuiButton,
        FormText,
        FormNumber,
        GuiContainer,
        LoadingProgress

    },

    data: () => ({
        form: form('/api/settings/general/get', '/api/settings/general/set'),
    }),

    computed: {
        processing() {
            return this.form.is_loading || this.form.is_saving;
        },
    },

    created() {
        this.form.toaster = this.$toast;
        this.form.load();
    },

    methods: {
        save() {
            if (!this.form.validate()) {
                return;
            }
            this.form.save();
        },
    }
}
</script>

<style lang="scss" scoped>
.settings-field {
    &__50:deep(.input-field__title) {
        width: 50%;
    }

    &__col {
        flex-direction: column;

        :deep(.input-field__title) {
            width: 100%;
        }
    }
}
</style>
