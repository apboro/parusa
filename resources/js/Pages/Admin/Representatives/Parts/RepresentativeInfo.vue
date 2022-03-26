<template>
    <div>
        <GuiContainer w-50 mt-30 inline>
            <GuiValue :title="'ФИО представителя'">{{ data['full_name'] }}</GuiValue>
            <GuiValue :title="'Дата заведения'">{{ data['created_at'] }}</GuiValue>
            <GuiValue :title="'Пол'">{{ data['gender'] }}</GuiValue>
            <GuiValue :title="'Дата рождения'">{{ data['birth_date'] }}</GuiValue>
            <GuiValue :title="'Email'"><a class="link" v-if="data['email']" target="_blank" :href="'mailto:'+data['email']">{{ data['email'] }}</a></GuiValue>
            <GuiValue :title="'Мобильный телефон'">{{ data['mobile_phone'] }}</GuiValue>
            <GuiValue :title="'Рабочий телефон'">{{ data['work_phone'] }}<span
                v-if="data['work_phone_additional']"> доб. {{ data['work_phone_additional'] }}</span></GuiValue>
            <GuiValue :title="'Должность по умолчанию'">{{ data['default_position_title'] }}</GuiValue>
        </GuiContainer>

        <GuiContainer w-50 mt-30 pl-40 inline>
            <GuiValue :class="'w-150px'" :title="'ВК'">{{ data['vkontakte'] }}</GuiValue>
            <GuiValue :class="'w-150px'" :title="'Facebook'">{{ data['facebook'] }}</GuiValue>
            <GuiValue :class="'w-150px'" :title="'Telegram'">{{ data['telegram'] }}</GuiValue>
            <GuiValue :class="'w-150px'" :title="'Skype'">{{ data['skype'] }}</GuiValue>
            <GuiValue :class="'w-150px'" :title="'WhatsApp'">{{ data['whatsapp'] }}</GuiValue>
        </GuiContainer>

        <GuiContainer w-100 mt-50>
            <GuiHeading mb-20>Связан с партнерами</GuiHeading>
            <ListTable v-if="data['positions'] && data['positions'].length > 0"
                       :titles="['Партнер', 'Должность', 'Рабочий телефон, email', 'Статус доступа']" :has-action="editable"
            >
                <ListTableRow v-for="position in data['positions']">
                    <ListTableCell>
                        <router-link :class="'link'" :to="{name: 'partners-view', params: {id: position['partner_id']}}">{{ position['partner'] }}</router-link>
                    </ListTableCell>
                    <ListTableCell>
                        {{ position['title'] }}
                    </ListTableCell>
                    <ListTableCell>
                        <div>
                            <a class="link" :href="'mailto:' + position['email']" target="_blank">{{ position['email'] }}</a>
                        </div>
                        <div>
                            {{ position['work_phone'] }}<span v-if="position['work_phone_additional']"> доб. {{ position['work_phone_additional'] }}</span>
                        </div>
                    </ListTableCell>
                    <ListTableCell v-if="editable">
                        <span class="link" @click="statusChange(position)"><GuiAccessIndicator :locked="!position['active']"/>{{ position['status'] }}</span>
                    </ListTableCell>
                    <ListTableCell v-else>
                        <GuiAccessIndicator :locked="!position['active']"/>
                        {{ position['status'] }}
                    </ListTableCell>
                    <ListTableCell v-if="editable">
                        <GuiActionsMenu :title="null">
                            <span class="link" @click="editPosition(position)">Редактировать</span>
                            <span class="link" @click="deletePosition(position)">Открепить</span>
                        </GuiActionsMenu>
                    </ListTableCell>
                </ListTableRow>
            </ListTable>
            <GuiMessage border v-else>Не связан ни с одним партнёром</GuiMessage>
            <div class="text-right mt-20" v-if="editable">
                <GuiButton @click="addPosition">Прикрепить к компании-партнеру</GuiButton>
            </div>
        </GuiContainer>

        <GuiContainer w-100 mt-30>
            <GuiValueArea :title="'Заметки'" v-text="data['notes']"/>
        </GuiContainer>

        <GuiContainer mt-15 v-if="editable">
            <GuiButton @click="edit">Редактировать</GuiButton>
        </GuiContainer>

        <FormPopUp :title="position === 0 ? 'Прикрепление представителя к компании' : 'Изменение данных'" v-if="editable"
                   :form="form"
                   :options="{id: representativeId, position_id: position}"
                   ref="position"
        >
            <GuiContainer w-600px>
                <FormDictionary :form="form" :name="'partner_id'" :dictionary="'partners'" :disabled="position !== 0" :search="true" :fresh="true"/>
                <FormString :form="form" :name="'title'"/>
                <FormPhone :form="form" :name="'work_phone'" :autocomplete="'nope'"/>
                <FormString :form="form" :name="'work_phone_additional'" :autocomplete="'nope'"/>
                <FormString :form="form" :name="'email'" :autocomplete="'nope'"/>
            </GuiContainer>
        </FormPopUp>

        <FormPopUp :title="status_title" v-if="editable"
                   :form="status_form"
                   :options="{position_id: position}"
                   ref="status"
        >
            <FormDictionary :form="status_form" :dictionary="'position_access_statuses'" :name="'status_id'" :hide-title="true"/>
        </FormPopUp>
    </div>
</template>

<script>


import DeleteEntry from "@/Mixins/DeleteEntry";
import GuiContainer from "@/Components/GUI/GuiContainer";
import GuiValue from "@/Components/GUI/GuiValue";
import GuiValueArea from "@/Components/GUI/GuiValueArea";
import GuiButton from "@/Components/GUI/GuiButton";
import GuiHeading from "@/Components/GUI/GuiHeading";
import ListTable from "@/Components/ListTable/ListTable";
import GuiMessage from "@/Components/GUI/GuiMessage";
import ListTableRow from "@/Components/ListTable/ListTableRow";
import ListTableCell from "@/Components/ListTable/ListTableCell";
import GuiActionsMenu from "@/Components/GUI/GuiActionsMenu";
import GuiAccessIndicator from "@/Components/GUI/GuiAccessIndicator";
import form from "@/Core/Form";
import FormPopUp from "@/Components/FormPopUp";
import FormDictionary from "@/Components/Form/FormDictionary";
import FormString from "@/Components/Form/FormString";
import FormPhone from "@/Components/Form/FormPhone";

export default {
    props: {
        representativeId: {type: Number, required: true},
        data: {type: Object, default: () => ({})},
        editable: {type: Boolean, default: false},
    },

    emits: ['update'],

    components: {
        FormPhone,
        FormString,
        FormDictionary,
        FormPopUp,
        GuiAccessIndicator,
        GuiActionsMenu,
        ListTableCell,
        ListTableRow,
        GuiMessage,
        ListTable,
        GuiHeading,
        GuiButton,
        GuiValueArea,
        GuiValue,
        GuiContainer
    },

    mixins: [DeleteEntry],

    data: () => ({
        form: form(null, '/api/representatives/attach'),
        position: 0,
        status_form: form(null, '/api/representatives/status'),
        status_title: null,
    }),

    created() {
        this.form.toaster = this.$toast;
        this.status_form.toaster = this.$toast;
    },

    methods: {
        edit() {
            this.$router.push({name: 'representatives-edit', params: {id: this.representativeId}});
        },
        addPosition() {
            this.position = 0;
            this.form.reset();
            this.form.set('partner_id', null, 'required', 'Компания', true);
            this.form.set('title', this.data['default_position_title'], 'required', 'Должность', true);
            this.form.set('work_phone', this.data['work_phone'], 'required', 'Рабочий телефон', true);
            this.form.set('work_phone_additional', this.data['work_phone_additional'], null, 'Доп. номер к раб. телефону', true);
            this.form.set('email', this.data['email'], 'required|email|bail', 'Email', true);
            this.form.load();

            this.$refs.position.show()
                .then(result => {
                    this.$emit('update', {positions: result.payload['positions']});
                });
        },
        editPosition(position) {
            this.position = position['position_id'];
            this.form.reset();
            this.form.set('partner_id', position['partner_id'], 'required', 'Компания', true);
            this.form.set('title', position['title'], 'required', 'Должность', true);
            this.form.set('work_phone', position['work_phone'], 'required', 'Рабочий телефон', true);
            this.form.set('work_phone_additional', position['work_phone_additional'], null, 'Доп. номер к раб. телефону', true);
            this.form.set('email', position['email'], 'required|email|bail', 'Email', true);
            this.form.load();

            this.$refs.position.show()
                .then(result => {
                    this.$emit('update', {positions: result.payload['positions']});
                });
        },
        deletePosition(position) {
            const position_id = position['position_id'];
            this.deleteEntry('Открепить представителя от компании "' + position['partner'] + '"?', '/api/representatives/detach',
                {id: this.representativeId, position_id: position_id}, 'Открепить')
                .then(() => {
                    let positions = this.data['positions'].filter(position => position['position_id'] !== position_id);
                    this.$emit('update', {positions: positions});
                });
        },
        statusChange(position) {
            this.status_title = 'Статус доступа в организации "' + position['partner'] + '"';
            this.position = position['position_id'];
            this.status_form.reset();
            this.status_form.set('status_id', position['status_id'], 'required', 'Статус доступа', true);
            this.status_form.load();
            this.$refs.status.show()
                .then(response => {
                    let positions = this.data['positions'].map(position => {
                        if (position['position_id'] === response.payload['position_id']) {
                            position['status'] = response.payload['status'];
                            position['status_id'] = response.payload['status_id'];
                            position['active'] = response.payload['active'];
                        }
                        return position;
                    })
                    this.$emit('update', {positions: positions});
                });
        },
    }
}
</script>
