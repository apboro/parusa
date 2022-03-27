<template>
    <div>
        <GuiContainer w-50 mt-30>
            <GuiValue :title="'Название партнера'">{{ data['name'] }}</GuiValue>
            <GuiValue :title="'Дата заведения'" v-if="editable">{{ data['created_at'] }}</GuiValue>
            <GuiValue :title="'Тип партнера'">{{ data['type'] }}</GuiValue>
            <GuiValue :title="'Статус'">
                <span class="link" v-if="editable" @click="statusChange"><GuiActivityIndicator :active="data['active']"/>{{ data['status'] }}</span>
                <span v-else><GuiActivityIndicator :active="data['active']"/>{{ data['status'] }}</span>
            </GuiValue>
        </GuiContainer>
        <GuiContainer w-50 mt-30>
            <GuiValue :title="'Билеты для гидов'">
                <span class="link" v-if="editable" @click="ticketsChange">{{ data['tickets_for_guides'] }}</span>
                <span v-else>{{ data['tickets_for_guides'] }}</span>
            </GuiValue>
            <GuiHint mt-5 mb-10>
                При значении "0" партнер не может включать в заказ бесплатные билеты для гидов. Любое положительное число разрешает данную возможность и определяет
                максимальное количество таких билетов для одного заказа. Например, при значении "1" к заказу можно будет добавить 1 билет для гида.
            </GuiHint>
            <GuiValue :title="'Бронирование билетов'" v-if="editable">
                <span class="link" v-if="editable" @click="reserveChange">{{ data['can_reserve_tickets'] === 1 ? 'Разрешено' : 'Запрещено' }}</span>
                <span v-else>{{ data['can_reserve_tickets'] }}</span>
            </GuiValue>
        </GuiContainer>

        <GuiContainer w-100 mt-30>
            <GuiValueArea :title="'Документы'">
                <GuiFilesList :files="data['documents']"/>
            </GuiValueArea>
        </GuiContainer>

        <GuiContainer w-100 mt-20 v-if="editable">
            <GuiValueArea :title="'Заметки'" v-text="data['notes']"/>
        </GuiContainer>

        <GuiContainer mt-15 v-if="editable">
            <GuiButton @click="edit">Редактировать</GuiButton>
        </GuiContainer>

        <FormPopUp :title="form_title"
                   :form="form"
                   :options="{id: partnerId}"
                   ref="popup"
        >
            <GuiContainer w-350px>
                <FormDictionary v-if="dictionary !== null && dictionary !== 'bool'" :form="form" :name="'value'" :dictionary="dictionary" :fresh="true" :hide-title="true"/>
                <FormDropdown v-else-if="dictionary !== null && dictionary === 'bool'" :form="form" :name="'value'" :identifier="'id'" :show="'name'"
                              :options="[{id: 0, name: 'Запрещено'}, {id: 1, name: 'Разрешено'}]"
                              :placeholder="'Бронирование билетов'"
                              :hide-title="true"
                />
                <FormNumber v-else :form="form" :name="'value'" :hide-title="true"/>
            </GuiContainer>
        </FormPopUp>
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
