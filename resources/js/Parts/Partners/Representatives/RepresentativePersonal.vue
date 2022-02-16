<template>
    <div>
        <container w-50 mt-30 inline>
            <value :title="'ФИО представителя'">{{ datasource.data['full_name'] }}</value>
            <value :title="'Дата заведения'">{{ datasource.data['created_at'] }}</value>
            <value :title="'Пол'">{{ datasource.data['gender'] }}</value>
            <value :title="'Дата рождения'">{{ datasource.data['birth_date'] }}</value>
            <value :title="'Email'"><a class="link" v-if="datasource.data.email" target="_blank" :href="'mailto:'+datasource.data.email">{{ datasource.data['email'] }}</a></value>
            <value :title="'Мобильный телефон'">{{ datasource.data['mobile_phone'] }}</value>
            <value :title="'Рабочий телефон'">{{ datasource.data['work_phone'] }}<span
                v-if="datasource.data['work_phone_additional']"> доб. {{ datasource.data['work_phone_additional'] }}</span></value>
            <value :title="'Должность по умолчанию'">{{ datasource.data['default_position_title'] }}</value>
        </container>

        <container w-50 mt-30 pl-40 inline>
            <value :class="'w-150px'" :title="'ВК'">{{ datasource.data['vkontakte'] }}</value>
            <value :class="'w-150px'" :title="'Facebook'">{{ datasource.data['facebook'] }}</value>
            <value :class="'w-150px'" :title="'Telegram'">{{ datasource.data['telegram'] }}</value>
            <value :class="'w-150px'" :title="'Skype'">{{ datasource.data['skype'] }}</value>
            <value :class="'w-150px'" :title="'WhatsApp'">{{ datasource.data['whatsapp'] }}</value>
        </container>

        <container w-100 mt-50>
            <heading mb-20>Связан с партнерами</heading>
            <base-table>
                <template v-slot:header>
                    <base-table-head :header="['Партнер', 'Должность', 'Рабочий телефон, email', 'Статус доступа']" :has-actions="editable"/>
                </template>
                <base-table-row v-for="(position, key) in datasource.data.positions" v-if="datasource.data.positions" :key="key">
                    <base-table-cell>
                        <router-link :class="'link'" :to="{name: 'partners-view', params: {id: position['partner_id']}}">{{ position['partner'] }}</router-link>
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
            <message v-if="!datasource.data.positions || datasource.data.positions.length === 0">Не связан ни с одним партнёром</message>
            <div class="text-right mt-20" v-if="editable">
                <span class="link" @click="attachPosition">Прикрепить к компании-партнеру</span>
            </div>
        </container>

        <container w-100 mt-50>
            <value-area :title="'Заметки'" v-text="datasource.data['notes']"/>
        </container>

        <container mt-15 v-if="editable">
            <base-link-button :to="{ name: 'representatives-edit', params: { id: representativeId }}">Редактировать</base-link-button>
        </container>

        <pop-up ref="popup" v-if="editable" :manual="true" :title="popup_title" :buttons="[
            {result: 'no', caption: 'Отмена', color: 'white'},
            {result: 'yes', caption: 'OK', color: 'green'}
        ]">
            <dictionary-drop-down :dictionary="'position_access_statuses'" :original="initial_status" v-model="current_status" :name="'status'"/>
        </pop-up>

        <pop-up ref="position" v-if="editable"
                :title="position_popup_title"
                :buttons="[{result: 'no', caption: 'Отмена', color: 'white'},{result: 'yes', caption: 'OK', color: 'green'}]"
                :resolving="positionFormResolving"
        >
            <container w-600px>
                <data-field-dictionary-dropdown :datasource="form"
                                                :name="'partner_id'"
                                                :dictionary="'partners'"
                                                :disabled="!position_change_partner"
                                                :search="true"
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
import Value from "../../../Components/GUI/GuiValue";
import Heading from "../../../Components/GUI/GuiHeading";
import AccessLocked from "../../../Components/GUI/GuiAccessIndicator";
import ActionsMenu from "../../../Components/GUI/GuiActionsMenu";
import Message from "@/Components/GUI/GuiMessage";
import ValueArea from "../../../Components/GUI/GuiValueArea";
import BaseLinkButton from "../../../Components/Base/BaseLinkButton";
import PopUp from "../../../Components/PopUp";
import DictionaryDropDown from "../../../Components/Dictionary/DictionaryDropDown";
import DataFieldDictionaryDropdown from "../../../Components/DataFields/DataFieldDictionaryDropdown";
import DataFieldInput from "../../../Components/DataFields/DataFieldInput";
import DataFieldMaskedInput from "../../../Components/DataFields/DataFieldMaskedInput";

export default {
    props: {
        representativeId: {type: Number, required: true},
        datasource: {type: Object},
        editable: {type: Boolean, default: false},
    },

    components: {
        DataFieldMaskedInput,
        Container,
        Value,
        Heading,
        AccessLocked,
        ActionsMenu,
        Message,
        ValueArea,
        BaseLinkButton,
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
        position_change_partner: true,
    }),

    created() {
        this.form = formDataSource(null, '/api/representatives/attach', {id: this.representativeId});
        this.form.toaster = this.$toast;
        this.form.afterSave = this.positionAfterSave;
        this.form.failedSave = this.positionFailSave;
        this.form.values = {
            partner: null,
            title: null,
            work_phone: null,
            work_phone_additional: null,
            email: null,
        };
        this.form.validation_rules = {
            partner_id: parseRules('required'),
            title: parseRules('required'),
            work_phone: parseRules('required'),
            work_phone_additional: parseRules(''),
            email: parseRules('required|email'),
        };
        this.form.titles = {
            partner_id: 'Компания',
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
            this.popup_title = 'Статус доступа в организации "' + position['partner'] + '"';
            this.$refs.popup.show()
                .then(result => {
                    if (result === 'yes') {
                        this.$refs.popup.process(true);
                        axios.post('/api/representatives/status', {id: this.representativeId, position_id: position['position_id'], status_id: this.current_status})
                            .then(response => {
                                this.$toast.success(response.data.message, 3000);
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
            this.deleteEntry('Открепить представителя от компании "' + position['partner'] + '"?', '/api/representatives/detach',
                {id: this.representativeId, position_id: position_id})
                .then(() => {
                    this.datasource.data['positions'] = this.datasource.data['positions'].filter(position => position['position_id'] !== position_id);
                });
        },

        positionFormResolving(result) {
            if (result !== 'yes') {
                return true;
            }
            if (this.form.validateAll()) {
                this.$refs.position.process(true);
                this.form.options = {representative_id: this.representativeId, id: 0};
                this.form.save();
            }
            return false;
        },

        positionAfterSave(payload) {
            this.datasource.data['positions'] = payload['positions'];
            this.form.loaded = false;
            this.$refs.position.hide();
        },
        positionFailSave() {
            this.$refs.position.process(false);
        },
        attachPosition() {
            this.form.values = {
                partner_id: null,
                title: this.datasource.data['default_position_title'],
                work_phone: this.datasource.data['work_phone'],
                work_phone_additional: this.datasource.data['work_phone_additional'],
                email: this.datasource.data['email'],
            };
            this.form.valid = {
                partner_id: true,
                title: true,
                work_phone: true,
                work_phone_additional: true,
                email: true,
            };
            this.position_popup_title = 'Прикрепление представителя к компании';
            this.position_change_partner = true;
            this.form.loaded = true;

            this.$refs.position.show()
                .then(result => {
                    if (result === 'yes') {
                        this.$refs.position.process(true);
                        this.form.options = {representative_id: this.representativeId, id: 0};
                        this.form.save();
                    } else {
                        this.form.loaded = false;
                        this.$refs.position.hide();
                    }
                });
        },

        editPosition(position) {
            this.form.values = {
                partner_id: position['partner_id'],
                title: position['title'],
                work_phone: position['work_phone'],
                work_phone_additional: position['work_phone_additional'],
                email: position['email'],
            };
            this.position_popup_title = 'Изменение записи';
            this.position_change_partner = false;
            this.form.loaded = true;

            this.$refs.position.show()
                .then(result => {
                    if (result === 'yes') {
                        this.$refs.position.process(true);
                        this.form.options = {representative_id: this.representativeId, id: position['position_id']};
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
