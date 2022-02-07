<template>
    <div>
        <container w-100>
            <div class="text-right mb-20" v-if="editable">
                <span class="link" @click="attachPosition">Прикрепить представителя</span>
            </div>
            <base-table>
                <template v-slot:header>
                    <base-table-head :header="['ФИО представителя', 'Должность', 'Рабочий телефон, email', 'Статус доступа']" :has-actions="editable"/>
                </template>
                <base-table-row v-for="(position, key) in datasource.data.positions" v-if="datasource.data.positions" :key="key">
                    <base-table-cell>
                        <router-link v-if="links" :class="'link'" :to="{name: 'representatives-view', params: {id: position['user_id']}}">{{ position['user'] }}</router-link>
                        <span v-else>{{ position['user'] }}</span>
                    </base-table-cell>
                    <base-table-cell>{{ position['title'] }}</base-table-cell>
                    <base-table-cell>
                        <base-table-cell-item>
                            <a class="link" :href="'mailto:' + position['email']" target="_blank">{{ position['email'] }}</a>
                        </base-table-cell-item>
                        <base-table-cell-item>{{ position['work_phone'] }}<span v-if="position['work_phone_additional']"> доб. {{ position['work_phone_additional'] }}</span>
                        </base-table-cell-item>
                    </base-table-cell>
                    <base-table-cell>
                        <span class="link" v-if="editable" @click="statusChange(position)"><access-locked :locked="!position['active']"/>{{ position['status'] }}</span>
                        <span v-else><access-locked :locked="!position['active']"/>{{ position['status'] }}</span>
                    </base-table-cell>
                    <base-table-cell v-if="editable">
                        <actions-menu :title="null">
                            <span class="link" @click="editPosition(position)">Редактировать</span>
                            <span class="link" @click="deletePosition(position)">Открепить</span>
                        </actions-menu>
                    </base-table-cell>
                </base-table-row>
            </base-table>
            <message v-if="!datasource.data.positions || datasource.data.positions.length === 0">Нет прикреплённых представителей</message>
        </container>

        <pop-up ref="popup" v-if="editable"
                :title="popup_title"
                :buttons="[{result: 'no', caption: 'Отмена', color: 'white'},{result: 'yes', caption: 'OK', color: 'green'}]"
                :manual="true"
        >
            <dictionary-drop-down :dictionary="'position_access_statuses'" :original="initial_status" v-model="current_status" :name="'status'"/>
        </pop-up>

        <pop-up ref="position" v-if="editable"
                :title="position_popup_title"
                :buttons="[{result: 'no', caption: 'Отмена', color: 'white'},{result: 'yes', caption: 'OK', color: 'green'}]"
                :resolving="positionFormResolving"
                :manual="true"
        >
            <container w-600px>
                <data-field-dictionary-dropdown :dictionary="'representatives'"
                                                :datasource="form"
                                                :name="'representative_id'"
                                                :disabled="!position_change_user"
                                                :search="true"
                                                @changed="representativeSelected"
                />
                <data-field-input :datasource="form" :name="'title'"/>
                <data-field-masked-input :datasource="form" :name="'work_phone'" :mask="'+7 (###) ###-##-##'"/>
                <data-field-input :datasource="form" :name="'work_phone_additional'"/>
                <data-field-input :datasource="form" :name="'email'"/>
            </container>
        </pop-up>
    </div>
</template>

<script>
import formDataSource from "../../../Helpers/Core/formDataSource";
import {parseRules} from "../../../Helpers/Core/validator/validator";
import UseBaseTableBundle from "../../../Mixins/UseBaseTableBundle";
import DeleteEntry from "../../../Mixins/DeleteEntry";

import Container from "../../../Components/GUI/GuiContainer";
import AccessLocked from "../../../Components/GUI/GuiAccessIndicator";
import ActionsMenu from "../../../Components/GUI/GuiActionsMenu";
import Message from "@/Components/GUI/GuiMessage";
import PopUp from "../../../Components/PopUp";
import DictionaryDropDown from "../../../Components/Dictionary/DictionaryDropDown";
import DataFieldDictionaryDropdown from "../../../Components/DataFields/DataFieldDictionaryDropdown";
import DataFieldInput from "../../../Components/DataFields/DataFieldInput";
import DataFieldMaskedInput from "../../../Components/DataFields/DataFieldMaskedInput";

export default {
    props: {
        partnerId: {type: Number, required: true},
        datasource: {type: Object},
        editable: {type: Boolean, default: false},
        links: {type: Boolean, default: true},
    },

    components: {
        DataFieldMaskedInput,
        Container,
        AccessLocked,
        ActionsMenu,
        Message,
        PopUp,
        DictionaryDropDown,
        DataFieldDictionaryDropdown,
        DataFieldInput,
    },

    mixins: [UseBaseTableBundle, DeleteEntry],

    data: () => ({
        initial_status: null,
        current_status: null,
        popup_title: null,
        form: null,
        position_popup_title: null,
        position_change_user: true,
    }),

    created() {
        this.form = formDataSource(null, '/api/partners/representative/attach', {id: this.partnerId});
        this.form.toaster = this.$toast;
        this.form.afterSave = this.positionAfterSave;
        this.form.validation_rules = {
            representative_id: parseRules('required'),
            title: parseRules('required'),
            work_phone: parseRules('required'),
            work_phone_additional: parseRules(''),
            email: parseRules('required|email'),
        };
        this.form.titles = {
            representative_id: 'Представитель',
            title: 'Должность',
            work_phone: 'Рабочий телефон',
            work_phone_additional: 'Доп. номер к раб. телефону',
            email: 'Email',
        };
    },

    methods: {
        statusChange(position) {
            this.initial_status = Number(position['status_id']);
            this.current_status = this.initial_status;
            this.popup_title = 'Статус доступа в для представителя "' + position['user'] + '"';
            this.$refs.popup.show()
                .then(result => {
                    if (result === 'yes') {
                        this.$refs.popup.process(true);
                        axios.post('/api/partners/representative/status', {id: this.partnerId, position_id: position['position_id'], status_id: this.current_status})
                            .then(response => {
                                this.$toast.success(response.data.message, 2000);
                                const position_id = response.data.data.position_id;
                                this.datasource.data['positions'].some(position => {
                                    if (position['position_id'] === position_id) {
                                        position.active = response.data.data.active;
                                        position.status = response.data.data.status;
                                        position.status_id = response.data.data.status_id;
                                        return true;
                                    }
                                    return false;
                                })
                            })
                            .catch(error => {
                                this.$toast.error(error.response.data.message);
                            })
                            .finally(() => {
                                this.initial_status = null;
                                this.current_status = null;
                                this.popup_title = null;
                                this.$refs.popup.hide();
                            })
                    } else {
                        this.initial_status = null;
                        this.current_status = null;
                        this.popup_title = null;
                        this.$refs.popup.hide();
                    }
                });
        },

        deletePosition(position) {

            const position_id = position['position_id'];
            this.deleteEntry('Открепить представителя "' + position['user'] + '"?', '/api/partners/representative/detach',
                {id: this.partnerId, position_id: position_id})
                .then(() => {
                    this.datasource.data['positions'] = this.datasource.data['positions'].filter(position => position['position_id'] !== position_id);
                });
        },

        positionFormResolving(result) {
            return result !== 'yes' || this.form.validateAll();
        },

        representativeSelected(name, value) {
            this.$refs.position.process(true);
            axios.post('/api/partners/representative/details', {id: value})
                .then(response => {
                    this.form.values.title = response.data.data['default_position_title'];
                    this.form.values.work_phone = response.data.data['work_phone'];
                    this.form.values.work_phone_additional = response.data.data['work_phone_additional'];
                    this.form.values.email = response.data.data['email'];
                })
                .catch(error => {
                    this.$toast.error(error.response.data.message);
                })
                .finally(() => {
                    this.$refs.position.process(false);
                });
        },

        positionAfterSave(payload) {
            this.datasource.data['positions'] = payload['positions'];
            this.form.loaded = false;
            this.$refs.position.hide();
        },

        attachPosition() {
            this.form.values = {representative_id: null, title: null, work_phone: null, work_phone_additional: null, email: null};
            this.form.originals = {representative_id: null, title: null, work_phone: null, work_phone_additional: null, email: null};
            this.position_popup_title = 'Прикрепление сотрудника';
            this.position_change_user = true;
            this.form.loaded = true;

            this.$refs.position.show()
                .then(result => {
                    if (result === 'yes') {
                        this.$refs.position.process(true);
                        this.form.options = {partner_id: this.partnerId, id: 0};
                        this.form.save();
                    } else {
                        this.form.loaded = false;
                        this.$refs.position.hide();
                    }
                });
        },

        editPosition(position) {
            this.form.values = {
                representative_id: position['user_id'],
                title: position['title'],
                work_phone: position['work_phone'],
                work_phone_additional: position['work_phone_additional'],
                email: position['email'],
            };
            this.form.originals = {
                representative_id: position['user_id'],
                title: position['title'],
                work_phone: position['work_phone'],
                work_phone_additional: position['work_phone_additional'],
                email: position['email'],
            };
            this.position_popup_title = 'Изменение записи';
            this.position_change_user = false;
            this.form.loaded = true;

            this.$refs.position.show()
                .then(result => {
                    if (result === 'yes') {
                        this.$refs.position.process(true);
                        this.form.options = {partner_id: this.partnerId, id: position['position_id']};
                        this.form.save();
                    } else {
                        this.form.loaded = false;
                        this.$refs.position.hide();
                    }
                });
        },
    }
}
</script>
