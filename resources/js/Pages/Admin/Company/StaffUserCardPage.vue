<template>
    <page :loading="data.loading">
        <template v-slot:header>
            <page-title-bar :title="data.data['full_name']" :breadcrumbs="[
                {caption: 'Сотрудники', to: {name: 'staff-user-list'}},
            ]">
                <actions-menu>
                    <span>Удалить сотрудника</span>
                </actions-menu>
            </page-title-bar>
        </template>

        <base-table-container :w="'50'">
            <base-table :borders="false" :highlight="false" :hover="false" :small="true">
                <base-table-row>
                    <base-table-cell :w="'200'">ФИО сотрудника</base-table-cell>
                    <base-table-cell :bold="true" :size="'lg'">{{ data.data.last_name }} {{ data.data.first_name }} {{
                            data.data.patronymic
                        }}
                    </base-table-cell>
                </base-table-row>
                <base-table-row>
                    <base-table-cell :w="'200'">Должность</base-table-cell>
                    <base-table-cell :bold="true" :size="'lg'">{{ data.data.position_title }}</base-table-cell>
                </base-table-row>
                <base-table-row>
                    <base-table-cell :w="'200'">Дата заведения</base-table-cell>
                    <base-table-cell :bold="true" :size="'lg'">{{ data.data.created_at }}</base-table-cell>
                </base-table-row>
                <base-table-row>
                    <base-table-cell :w="'200'">Статус трудоустройства</base-table-cell>
                    <base-table-cell :bold="true" :size="'lg'">{{ data.data.position_status }}</base-table-cell>
                </base-table-row>
            </base-table>
        </base-table-container>

        <base-table-container :w="'50'">
            <base-table :borders="false" :highlight="false" :hover="false" :small="true">
                <base-table-row>
                    <base-table-cell :w="'200'">Дата рождения</base-table-cell>
                    <base-table-cell :bold="true" :size="'lg'">{{ data.data.birth_date }}</base-table-cell>
                </base-table-row>
                <base-table-row>
                    <base-table-cell :w="'200'">Пол</base-table-cell>
                    <base-table-cell :bold="true" :size="'lg'">{{ data.data.gender }}</base-table-cell>
                </base-table-row>
            </base-table>
        </base-table-container>

        <container :no-bottom="true">
            <base-link-button :to="{ name: 'staff-user-edit', params: { id: userId }}">Редактировать
            </base-link-button>
        </container>

    </page>
</template>

<script>
import genericDataSource from "../../../Helpers/Core/genericDataSource";

import Page from "../../../Layouts/Page";
import BaseButton from "../../../Components/Base/BaseButton";
import UseBaseTableBundle from "../../../Mixins/UseBaseTableBundle";
import Container from "../../../Layouts/Parts/Container";
import BaseLinkButton from "../../../Components/Base/BaseLinkButton";
import PageTitleBar from "../../../Layouts/Parts/PageTitleBar";
import ActionsMenu from "../../../Components/ActionsMenu";

export default {
    components: {
        ActionsMenu,
        PageTitleBar,
        Page,
        BaseButton,
        Container,
        BaseLinkButton,
    },

    mixins: [UseBaseTableBundle],

    data: () => ({
        data: null,
    }),

    computed: {
        userId() {
            return this.$route.params.id;
        }
    },

    created() {
        this.data = genericDataSource('/api/users/staff/view');
        this.data.load({id: this.userId});
    },

    methods: {}
}
</script>
