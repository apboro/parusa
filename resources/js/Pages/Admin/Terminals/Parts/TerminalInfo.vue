<template>
    <div>
        <GuiContainer mt-30>
            <GuiValue :title="'Номер кассы'">{{ data['id'] }}</GuiValue>
            <GuiValue :title="'Причал'">{{ data['pier'] }}</GuiValue>
            <GuiValue :title="'Адрес'">{{ data['address'] }}</GuiValue>
            <GuiValue :title="'Статус'">
                <span class="link" v-if="editable" @click="statusChange"><GuiActivityIndicator :active="data['active']"/>{{ data['status'] }}</span>
                <span v-else><GuiActivityIndicator :active="data['active']"/>{{ data['status'] }}</span>
            </GuiValue>
        </GuiContainer>
        <GuiContainer mt-30>
            <GuiValue :title="'Внешний ID мобильной кассы'">{{ data['workplace_id'] }}</GuiValue>
            <GuiValue :title="'Внешний ID торговой точки'">{{ data['outlet_id'] }}</GuiValue>
            <!--<GuiValue :title="'Внешний ID организации'">{{ data['organization_id'] }}</GuiValue>-->
        </GuiContainer>

        <GuiContainer mt-15 v-if="editable">
            <GuiButton @click="edit">Редактировать</GuiButton>
        </GuiContainer>

        <FormPopUp :title="'Статус кассы'"
                   :form="form"
                   :options="{id: terminalId}"
                   ref="popup"
        >
            <GuiContainer w-350px>
                <FormDictionary :form="form" :name="'value'" :dictionary="'terminal_statuses'" :hide-title="true"/>
            </GuiContainer>
        </FormPopUp>
    </div>
</template>

<script>
import GuiContainer from "@/Components/GUI/GuiContainer";
import GuiValue from "@/Components/GUI/GuiValue";
import GuiActivityIndicator from "@/Components/GUI/GuiActivityIndicator";
import GuiButton from "@/Components/GUI/GuiButton";
import FormPopUp from "@/Components/FormPopUp";
import FormDictionary from "@/Components/Form/FormDictionary";
import form from "@/Core/Form";

export default {
    props: {
        terminalId: {type: Number, required: true},
        data: {type: Object},
        editable: {type: Boolean, default: false},
    },

    emits: ['update'],

    components: {
        FormDictionary,
        FormPopUp,
        GuiContainer,
        GuiValue,
        GuiActivityIndicator,
        GuiButton,
    },

    data: () => ({
        form: form(null, '/api/terminals/properties'),
    }),

    methods: {
        edit() {
            this.$router.push({name: 'terminals-edit', params: {id: this.terminalId}});
        },
        statusChange() {
            this.form.reset();
            this.form.set('name', 'status_id');
            this.form.set('value', this.data['status_id'], 'required', 'Статус кассы', true);
            this.form.toaster = this.$toast;
            this.form.load();
            this.$refs.popup.show()
                .then(response => {
                    this.$emit('update', response.payload);
                });
        },
    }
}
</script>
