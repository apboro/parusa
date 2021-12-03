<template>
    <page>
        <template v-slot:header>{{ $route.meta.title }}</template>

        <loading-progress :loading="data.loading">

            <base-table>
                <base-table-row>
                    <base-table-cell>ФИО сотрудника</base-table-cell>
                    <base-table-cell>{{ data.data.last_name }} {{ data.data.first_name }} {{ data.data.patronymic }}</base-table-cell>
                </base-table-row>
                <base-table-row>
                    <base-table-cell>Должность</base-table-cell>
                    <base-table-cell>{{ data.data.position_title }}</base-table-cell>
                </base-table-row>
                <base-table-row>
                    <base-table-cell>Дата заведения</base-table-cell>
                    <base-table-cell>{{ data.data.created_at }}</base-table-cell>
                </base-table-row>
                <base-table-row>
                    <base-table-cell>Статус трудоустройства</base-table-cell>
                    <base-table-cell>{{ data.data.position_status }}</base-table-cell>
                </base-table-row>
                <base-table-row>
                    <base-table-cell>Пол</base-table-cell>
                    <base-table-cell>{{ data.data.gender }}</base-table-cell>
                </base-table-row>
                <base-table-row>
                    <base-table-cell>Дата рождения</base-table-cell>
                    <base-table-cell>{{ data.data.birth_date }}</base-table-cell>
                </base-table-row>
            </base-table>

            <router-link :class="'button'" :to="{ name: 'staff-user-edit', params: { id: userId }}">Редактировать</router-link>
        </loading-progress>
    </page>
</template>

<script>
import genericDataSource from "../../../Helpers/Core/genericDataSource";

import Page from "../../../Layouts/Page";
import LoadingProgress from "../../../Components/LoadingProgress";
import BaseButton from "../../../Components/Base/BaseButton";
import UseBaseTableBundle from "../../../Mixins/UseBaseTableBundle";

export default {
    components: {
        Page,
        BaseButton,
        LoadingProgress,
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
