<template>
    <LayoutPage :loading="processing" :title="form.payload['name']"
                :breadcrumbs="[{caption: 'Мобильные кассы', to: {name: 'terminals-view'}, params: {id: terminalId}}]"
                :link="{name: 'terminals-list'}"
                :link-title="'К списку мобильных касс'"
    >
        <GuiContainer mt-30>
            <FormDictionary :form="form" :dictionary="'terminal_statuses'" :name="'status_id'" :fresh="true"/>
            <FormDictionary :form="form" :dictionary="'piers'" :name="'pier_id'" :fresh="true" :search="true"/>
        </GuiContainer>

        <GuiContainer mt-30>
            <GuiButton @click="save" :color="'green'">Сохранить</GuiButton>
            <GuiButton @click="cancel">Отмена</GuiButton>
        </GuiContainer>
    </LayoutPage>
</template>

<script>
import form from "@/Core/Form";
import LayoutPage from "@/Components/Layout/LayoutPage";
import GuiContainer from "@/Components/GUI/GuiContainer";
import FormDictionary from "@/Components/Form/FormDictionary";
import GuiButton from "@/Components/GUI/GuiButton";

export default {
    components: {
        GuiButton,
        FormDictionary,
        GuiContainer,
        LayoutPage,
    },

    data: () => ({
        form: form('/api/terminals/get', '/api/terminals/update'),
    }),

    computed: {
        terminalId() {
            return Number(this.$route.params.id);
        },
        processing() {
            return this.form.is_loading || this.form.is_saving;
        },
    },

    created() {
        this.form.toaster = this.$toast;
        this.form.load({id: this.terminalId});
    },

    methods: {
        save() {
            if (!this.form.validate()) {
                return;
            }
            this.form.save({id: this.terminalId})
                .then(response => {
                    if (this.terminalId === 0) {
                        this.$router.push({name: 'terminals-view', params: {id: response.payload['id']}});
                    } else {
                        this.$router.push({name: 'terminals-view', params: {id: this.terminalId}});
                    }
                })
        },
        cancel() {
            if (this.terminalId === 0) {
                this.$router.push({name: 'terminals-list'});
            } else {
                this.$router.push({name: 'terminals-view', params: {id: this.terminalId}})
            }
        },
    }
}
</script>

