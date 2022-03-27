<template>
    <div>
        <GuiContainer w-100>
            <div class="text-right mb-20" v-if="editable">
                <GuiButton @click="addPosition">Прикрепить представителя</GuiButton>
            </div>

            <ListTable v-if="data['positions'] && data['positions'].length > 0"
                       :titles="['ФИО представителя', 'Должность', 'Рабочий телефон, email', 'Статус доступа']"
                       :has-action="editable"
            >
                <ListTableRow v-for="position in data['positions']">
                    <ListTableCell>
                        <router-link :class="'link'" :to="{name: 'representatives-view', params: {id: position['user_id']}}">{{ position['user'] }}</router-link>
                    </ListTableCell>
                    <ListTableCell>
                        {{ position['title'] }}
                    </ListTableCell>
                    <ListTableCell>
                        <div>
                            <a class="link" :href="'mailto:' + position['email']" target="_blank">{{ position['email'] }}</a>
                        </div>
                        <div>{{ position['work_phone'] }}<span v-if="position['work_phone_additional']"> доб. {{ position['work_phone_additional'] }}</span>
                        </div>
                    </ListTableCell>
                    <ListTableCell>
                        <span class="link" v-if="editable" @click="statusChange(position)"><GuiAccessIndicator :locked="!position['active']"/>{{ position['status'] }}</span>
                        <span v-else><GuiAccessIndicator :locked="!position['active']"/>{{ position['status'] }}</span>
                    </ListTableCell>
                    <ListTableCell v-if="editable">
                        <GuiActionsMenu :title="null">
                            <span class="link" @click="editPosition(position)">Редактировать</span>
                            <span class="link" @click="deletePosition(position)">Открепить</span>
                        </GuiActionsMenu>
                    </ListTableCell>
                </ListTableRow>
            </ListTable>

            <GuiMessage border v-else>Нет прикреплённых представителей</GuiMessage>
        </GuiContainer>

        <FormPopUp :title="position === 0 ? 'Прикрепление представителя к компании' : 'Изменение данных'" v-if="editable"
                   :form="form"
                   :options="{partner_id: partnerId, position_id: position}"
                   ref="position"
        >
            <GuiContainer w-600px>
                <FormDictionary :form="form" :name="'representative_id'"
                                :dictionary="'representatives'"
                                :disabled="position !== 0"
                                :search="true"
                                :fresh="true"
                                @change="representativeSelected"
                />
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
import DeleteEntry from "../../../../Mixins/DeleteEntry";
import GuiContainer from "@/Components/GUI/GuiContainer";
import GuiButton from "@/Components/GUI/GuiButton";
import ListTable from "@/Components/ListTable/ListTable";
import GuiMessage from "@/Components/GUI/GuiMessage";
import ListTableRow from "@/Components/ListTable/ListTableRow";
import ListTableCell from "@/Components/ListTable/ListTableCell";
import GuiActionsMenu from "@/Components/GUI/GuiActionsMenu";
import GuiAccessIndicator from "@/Components/GUI/GuiAccessIndicator";
import FormPopUp from "@/Components/FormPopUp";
import form from "@/Core/Form";
import FormDictionary from "@/Components/Form/FormDictionary";
import FormString from "@/Components/Form/FormString";
import FormPhone from "@/Components/Form/FormPhone";

export default {
    props: {
        partnerId: {type: Number, required: true},
        data: {type: Object},
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
        GuiButton,
        GuiContainer

    },

    mixins: [DeleteEntry],

    data: () => ({
        form: form(null, '/api/partners/representative/attach'),
        position: 0,
        status_form: form(null, '/api/partners/representative/status'),
        status_title: null,
    }),

    created() {
        this.form.toaster = this.$toast;
    },

    methods: {
        addPosition() {
            this.position = 0;
            this.form.reset();
            this.form.set('representative_id', null, 'required', 'Представитель', true);
            this.form.set('title', null, 'required', 'Должность', true);
            this.form.set('work_phone', null, 'required', 'Рабочий телефон', true);
            this.form.set('work_phone_additional', null, null, 'Доп. номер к раб. телефону', true);
            this.form.set('email', null, 'required|email|bail', 'Email', true);
            this.form.load();

            this.$refs.position.show()
                .then(result => {
                    this.$emit('update', {positions: result.payload['positions']});
                });
        },
        editPosition(position) {
            this.position = position['position_id'];
            this.form.reset();
            this.form.set('representative_id', position['user_id'], 'required', 'Компания', true);
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
            this.deleteEntry('Открепить представителя "' + position['user'] + '"?', '/api/partners/representative/detach',
                {id: this.partnerId, position_id: position_id}, 'Открепить')
                .then(() => {
                    let positions = this.data['positions'].filter(position => position['position_id'] !== position_id);
                    this.$emit('update', {positions: positions});
                });
        },
        representativeSelected(value) {
            this.$refs.position.process(true);
            axios.post('/api/partners/representative/details', {id: value})
                .then(response => {
                    this.form.set('title', response.data.data['default_position_title'], 'required', 'Должность', true);
                    this.form.set('work_phone', response.data.data['work_phone'], 'required', 'Рабочий телефон', true);
                    this.form.set('work_phone_additional', response.data.data['work_phone_additional'], null, 'Доп. номер к раб. телефону', true);
                    this.form.set('email', response.data.data['email'], 'required|email|bail', 'Email', true);
                })
                .catch(error => {
                    this.$toast.error(error.response.data.message);
                })
                .finally(() => {
                    this.$refs.position.process(false);
                });
        },

        statusChange(position) {
            this.status_title = 'Статус доступа представителя ' + position['user'];
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
        // statusChange(position) {
        // this.initial_status = Number(position['status_id']);
        // this.current_status = this.initial_status;
        // this.popup_title = 'Статус доступа в для представителя "' + position['user'] + '"';
        // this.$refs.popup.show()
        //     .then(result => {
        //         if (result === 'yes') {
        //             this.$refs.popup.process(true);
        //             axios.post('/api/partners/representative/status', {id: this.partnerId, position_id: position['position_id'], status_id: this.current_status})
        //                 .then(response => {
        //                     this.$toast.success(response.data.message, 2000);
        //                     const position_id = response.data.data.position_id;
        //                     this.datasource.data['positions'].some(position => {
        //                         if (position['position_id'] === position_id) {
        //                             position.active = response.data.data.active;
        //                             position.status = response.data.data.status;
        //                             position.status_id = response.data.data.status_id;
        //                             return true;
        //                         }
        //                         return false;
        //                     })
        //                 })
        //                 .catch(error => {
        //                     this.$toast.error(error.response.data.message);
        //                 })
        //                 .finally(() => {
        //                     this.initial_status = null;
        //                     this.current_status = null;
        //                     this.popup_title = null;
        //                     this.$refs.popup.hide();
        //                 })
        //         } else {
        //             this.initial_status = null;
        //             this.current_status = null;
        //             this.popup_title = null;
        //             this.$refs.popup.hide();
        //         }
        //     });
        // },
    }
}
</script>
