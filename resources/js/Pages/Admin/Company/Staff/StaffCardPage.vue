<template>
    <page :loading="processing">
        <template v-slot:header>
            <page-title-bar :title="data.data['full_name']"
                            :breadcrumbs="[{caption: 'Сотрудники', to: {name: 'staff-list'}}]"
                            :link="{name: 'staff-list'}"
                            :link-title="'К списку сотрудников'"
            >
                <actions-menu>
                    <span class="link" @click="deleteStaff">Удалить сотрудника</span>
                </actions-menu>
            </page-title-bar>
        </template>

        <layout-routed-tabs :tabs="{personal: 'Персональные данные', access: 'Доступ'}" @change="tab = $event"/>

        <staff-personal v-if="tab === 'personal'" :staff-id="staffId" :datasource="data" :editable="true"/>

        <container v-if="tab === 'access'">
            <loading-progress :loading="access_updating || form.saving">
                <message v-if="!data.data['active']">Для открытия доступа смените статус трудоустройства на “Действующий”.</message>
                <template v-else-if="data.data['has_access']">
                    <value :title="'Доступ активирован для логина'" :dots="false" :class="'w-230px'" mt-30 mb-20><b>{{ data.data['login'] }}</b></value>
                    <base-button @click="closeAccess" :color="'red'">Закрыть доступ в систему</base-button>
                </template>
                <container w-50 mt-20 v-else>
                    <data-field-input :name="'login'" :datasource="form"/>
                    <data-field-input :name="'password'" :datasource="form" :type="'password'"/>
                    <data-field-input :name="'password_confirmation'" :datasource="form" :type="'password'"/>
                    <base-button :class="'mt-20'" @click="openAccess">Открыть доступ в систему</base-button>
                </container>
            </loading-progress>
        </container>

    </page>
</template>

<script>
import genericDataSource from "../../../../Helpers/Core/genericDataSource";
import formDataSource from "../../../../Helpers/Core/formDataSource";
import {parseRules} from "../../../../Helpers/Core/validator/validator";
import DeleteEntry from "../../../../Mixins/DeleteEntry";

import Page from "../../../../Layouts/Page";
import PageTitleBar from "../../../../Layouts/Parts/PageTitleBar";
import ActionsMenu from "../../../../Components/GUI/GuiActionsMenu";
import LayoutRoutedTabs from "../../../../Components/Layout/LayoutRoutedTabs";
import StaffPersonal from "../../../../Parts/Company/Staff/StaffPersonal";
import Container from "../../../../Components/GUI/GuiContainer";
import LoadingProgress from "../../../../Components/LoadingProgress";
import Message from "@/Components/GUI/GuiMessage";
import Value from "../../../../Components/GUI/GuiValue";
import BaseButton from "../../../../Components/GUI/GuiButton";
import DataFieldInput from "../../../../Components/DataFields/DataFieldInput";

export default {
    components: {
        Page,
        PageTitleBar,
        ActionsMenu,
        LayoutRoutedTabs,
        StaffPersonal,
        LoadingProgress,
        Container,
        Message,
        Value,
        BaseButton,
        DataFieldInput,
    },

    mixins: [DeleteEntry],

    data: () => ({
        tab: null,
        data: null,
        deleting: false,
        access_updating: false,
        form: null,
    }),

    computed: {
        staffId() {
            return Number(this.$route.params.id);
        },

        processing() {
            return this.deleting || this.data.loading;
        },
    },

    created() {
        this.data = genericDataSource('/api/company/staff/view');
        this.form = formDataSource(null, '/api/company/staff/access/set', {id: this.staffId});
        this.form.toaster = this.$toast;
        this.form.afterSave = this.afterSave;
        this.data.onLoad = (data) => {
            this.form.values['login'] = data.email;
            this.form.values['password'] = '';
            this.form.values['password_confirmation'] = '';
            this.form.validation_rules['login'] = parseRules('required|min:6');
            this.form.validation_rules['password'] = parseRules('required|min:6|bail');
            this.form.validation_rules['password_confirmation'] = parseRules('same:password');
            this.form.titles['login'] = 'Логин';
            this.form.titles['password'] = 'Новый пароль';
            this.form.titles['password_confirmation'] = 'Подтверждение пароля';
            this.form.loaded = true
        };
        this.data.load({id: this.staffId});
    },

    methods: {
        deleteStaff() {
            const name = this.data.data['full_name'];

            this.deleteEntry('Удалить сотрудника "' + name + '"?', '/api/company/staff/delete', {id: this.staffId})
                .then(() => {
                    this.$router.push({name: 'staff-list'});
                });
        },

        closeAccess() {
            const name = this.data.data['full_name'];
            this.$dialog.show('Закрыть доступ в систему для сотрудника "' + name + '"?', 'question', 'red', [
                this.$dialog.button('no', 'Отмена', 'blue'),
                this.$dialog.button('yes', 'Продолжить', 'red'),
            ]).then(result => {
                if (result === 'yes') {
                    this.access_updating = true;
                    axios.post('/api/company/staff/access/release', {id: this.staffId})
                        .then(response => {
                            this.data.data['has_access'] = response.data.data.has_access;
                            this.data.data['login'] = response.data.data.login;
                            this.$toast.success(response.data.message, 3000);
                        })
                        .catch(error => {
                            this.$toast.error(error.response.data.message, 5000);
                        })
                        .finally(() => {
                            this.access_updating = false;
                        });
                }
            });
        },

        openAccess() {
            if (!this.form.validateAll()) {
                return;
            }

            this.form.save()
        },

        afterSave(payload) {
            this.data.data.has_access = payload.has_access;
            this.data.data.login = payload.login;
        }
    }
}
</script>
