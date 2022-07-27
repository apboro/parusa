<template>
    <div>
        <GuiContainer w-50 mt-30>
            <GuiValue :title="'ФИО сотрудника'">{{ data['full_name'] }}</GuiValue>
            <GuiValue :title="'Должность'">{{ data['position'] }}</GuiValue>
            <GuiValue :title="'Дата заведения'">{{ data['created_at'] }}</GuiValue>
            <GuiValue :title="'Статус трудоустройства'">
                <span class="link" v-if="editable" @click="statusChange"><GuiActivityIndicator :active="data['active']"/>{{ data['status'] }}</span>
                <span v-else><GuiActivityIndicator :active="data.active"/>{{ data['status'] }}</span>
            </GuiValue>
            <GuiValue :title="'Внешний ID'">{{ data['external_id'] }}</GuiValue>
        </GuiContainer>

        <GuiContainer w-50 mt-30 inline>
            <GuiValue :title="'Дата рождения'">{{ data['birth_date'] }}</GuiValue>
            <GuiValue :title="'Пол'">{{ data['gender'] }}</GuiValue>
            <GuiValue :title="'Email'">
                <a class="link" v-if="data.email" target="_blank" :href="'mailto:'+data.email">{{ data['email'] }}</a>
            </GuiValue>
            <GuiValue :title="'Рабочий телефон'">{{ data['work_phone'] }}
                <span v-if="data['work_phone_additional']"> доб. {{ data['work_phone_additional'] }}</span>
            </GuiValue>
            <GuiValue :title="'Мобильный телефон'">{{ data['mobile_phone'] }}</GuiValue>
        </GuiContainer>

        <GuiContainer w-50 mt-30 pl-40 inline>
            <GuiValue :class="'w-150px'" :title="'ВК'">{{ data['vkontakte'] }}</GuiValue>
            <GuiValue :class="'w-150px'" :title="'Facebook'">{{ data['facebook'] }}</GuiValue>
            <GuiValue :class="'w-150px'" :title="'Telegram'">{{ data['telegram'] }}</GuiValue>
            <GuiValue :class="'w-150px'" :title="'Skype'">{{ data['skype'] }}</GuiValue>
            <GuiValue :class="'w-150px'" :title="'WhatsApp'">{{ data['whatsapp'] }}</GuiValue>
        </GuiContainer>

        <GuiContainer w-100 mt-30>
            <GuiValueArea :title="'Заметки'" v-text="data['notes']"/>
        </GuiContainer>

        <GuiContainer mt-15 v-if="editable">
            <GuiButton @click="edit">Редактировать</GuiButton>
        </GuiContainer>

        <FormPopUp :title="'Статус сотрудника'"
                   :form="form"
                   :options="{id: staffId}"
                   ref="popup"
        >
            <GuiContainer w-350px>
                <FormDictionary :form="form" :name="'value'" :dictionary="'position_statuses'" :hide-title="true"/>
            </GuiContainer>
        </FormPopUp>
    </div>
</template>

<script>
import GuiContainer from "@/Components/GUI/GuiContainer";
import GuiValue from "@/Components/GUI/GuiValue";
import GuiValueArea from "@/Components/GUI/GuiValueArea";
import GuiActivityIndicator from "@/Components/GUI/GuiActivityIndicator";
import GuiButton from "@/Components/GUI/GuiButton";
import FormPopUp from "@/Components/FormPopUp";
import FormDictionary from "@/Components/Form/FormDictionary";
import form from "@/Core/Form";

export default {
    props: {
        staffId: {type: Number, required: true},
        data: {type: Object, required: true},
        editable: {type: Boolean, default: false},
    },

    emits: ['update'],

    components: {
        FormDictionary,
        FormPopUp,
        GuiButton,
        GuiActivityIndicator,
        GuiValueArea,
        GuiValue,
        GuiContainer
    },

    data: () => ({
        form: form(null, '/api/staff/properties'),
    }),

    methods: {
        edit() {
            this.$router.push({name: 'staff-edit', params: {id: this.staffId}});
        },
        statusChange() {
            this.form.reset();
            this.form.set('name', 'status_id');
            this.form.set('value', this.data['status_id'], 'required', 'Статус сотрудника', true);
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
