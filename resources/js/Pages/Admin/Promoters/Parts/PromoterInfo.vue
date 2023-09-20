<template>
    <div>
        <GuiContainer w-50 mt-30>
            <GuiValue :title="'ФИО промоутера'">{{ data['name'] }}</GuiValue>
            <GuiValue :title="'Дата заведения'" v-if="editable">{{ data['created_at'] }}</GuiValue>
        </GuiContainer>

        <GuiContainer w-100 mt-20 v-if="editable">
            <GuiValueArea :title="'Заметки'" v-text="data['notes']"/>
        </GuiContainer>

        <GuiContainer mt-15 v-if="editable">
            <GuiButton @click="edit">Редактировать</GuiButton>
        </GuiContainer>

    </div>
</template>

<script>
import form from "@/Core/Form";
import GuiContainer from "@/Components/GUI/GuiContainer";
import GuiValue from "@/Components/GUI/GuiValue";
import GuiValueArea from "@/Components/GUI/GuiValueArea";
import GuiButton from "@/Components/GUI/GuiButton";
import GuiActivityIndicator from "@/Components/GUI/GuiActivityIndicator";
import GuiHint from "@/Components/GUI/GuiHint";
import GuiFilesList from "@/Components/GUI/GuiFilesList";
import FormPopUp from "@/Components/FormPopUp";
import FormDictionary from "@/Components/Form/FormDictionary";
import FormNumber from "@/Components/Form/FormNumber";
import FormDropdown from "@/Components/Form/FormDropdown";

export default {
    props: {
        partnerId: {type: Number, required: true},
        data: {type: Object},
        editable: {type: Boolean, default: false},
    },

    emits: ['update'],

    components: {
        FormDropdown,
        FormNumber,
        FormDictionary,
        FormPopUp,
        GuiFilesList,
        GuiHint,
        GuiActivityIndicator,
        GuiButton,
        GuiValueArea,
        GuiValue,
        GuiContainer

    },

    data: () => ({
        form: form(null, '/api/partners/properties'),
        form_title: null,
        dictionary: null,
    }),

    methods: {
        edit() {
            this.$router.push({name: 'partners-edit', params: {id: this.partnerId}});
        },
        showForm(title, key, rules, dictionary = null) {
            this.form_title = title;
            this.form.reset();
            this.form.set('name', key);
            this.form.set('value', this.data[key], rules, title, true);
            this.dictionary = dictionary;
            this.form.toaster = this.$toast;
            this.form.load();
            this.$refs.popup.show()
                .then(response => {
                    this.$emit('update', response.payload);
                })
        },

        statusChange() {
            this.showForm('Изменить статус партнёра', 'status_id', 'required', 'partner_statuses');
        },

        ticketsChange() {
            this.showForm('Билеты для гидов', 'tickets_for_guides', 'required', null);
        },

        reserveChange() {
            this.showForm('Бронирование билетов', 'can_reserve_tickets', 'required', 'bool');
        },
    }
}
</script>
