<template>
    <LayoutPage :loading="processing" :title="form.payload['title']"
                :breadcrumbs="[{caption: 'Партнёры', to: {name: 'partners-list'}}]"
                :link="{name: 'partners-list'}"
                :link-title="'К списку партнёров'"
    >
        <GuiContainer mt-30>
            <FormString :form="form" :name="'name'"/>
            <FormDictionary :form="form" :dictionary="'partner_types'" :name="'type_id'"/>
            <FormDictionary :form="form" :dictionary="'position_statuses'" :name="'status_id'"/>
            <FormNumber :form="form" :name="'tickets_for_guides'"/>
            <FormDropdown :form="form" :name="'can_send_sms'" :identifier="'id'" :show="'name'" :options="[
                {id: 0, name: 'Запрещена'},
                {id: 1, name: 'Разрешена'},
            ]" :placeholder="'Отправка СМС'"/>
            <FormDropdown :form="form" :name="'can_reserve_tickets'" :identifier="'id'" :show="'name'" :options="[
                {id: 0, name: 'Запрещено'},
                {id: 1, name: 'Разрешено'},
            ]" :placeholder="'Бронирование билетов'"/>
        </GuiContainer>

        <GuiContainer mt-30>
            <FormFiles :form="form" :name="'documents'"/>
        </GuiContainer>

        <GuiContainer mt-30>
            <FormText :form="form" :name="'notes'"/>
        </GuiContainer>

        <GuiContainer mt-30>
            <GuiButton @click="save" :color="'green'">Сохранить</GuiButton>
            <GuiButton @click="cancel">Отмена</GuiButton>
        </GuiContainer>
    </LayoutPage>
</template>

<script>
import LayoutPage from "@/Components/Layout/LayoutPage";
import GuiContainer from "@/Components/GUI/GuiContainer";
import FormString from "@/Components/Form/FormString";
import FormDictionary from "@/Components/Form/FormDictionary";
import FormNumber from "@/Components/Form/FormNumber";
import FormDropdown from "@/Components/Form/FormDropdown";
import GuiButton from "@/Components/GUI/GuiButton";
import FormText from "@/Components/Form/FormText";
import FormFiles from "@/Components/Form/FormFiles";
import form from "@/Core/Form";

export default {
    components: {
        FormFiles,
        FormText,
        GuiButton,
        FormDropdown,
        FormNumber,
        FormDictionary,
        FormString,
        GuiContainer,
        LayoutPage

    },

    data: () => ({
        form: form('/api/partners/get', '/api/partners/update'),
    }),

    computed: {
        partnerId() {
            return Number(this.$route.params.id);
        },
        processing() {
            return this.form.is_loading || this.form.is_saving;
        },
    },

    created() {
        this.form.toaster = this.$toast;
        this.form.load({id: this.partnerId});
    },

    methods: {
        save() {
            if (!this.form.validate()) {
                return;
            }
            this.form.save({id: this.partnerId})
                .then(response => {
                    if (this.partnerId === 0) {
                        this.$router.push({name: 'partners-view', params: {id: response.payload['id']}});
                    } else {
                        this.$router.push({name: 'partners-view', params: {id: this.partnerId}});
                    }
                })
        },
        cancel() {
            if (this.partnerId === 0) {
                this.$router.push({name: 'partners-list'});
            } else {
                this.$router.push({name: 'partners-view', params: {id: this.partnerId}})
            }
        },
    }
}
</script>
