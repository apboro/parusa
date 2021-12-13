<template>
    <page :loading="processing">
        <template v-slot:header>
            <page-title-bar :title="data.data['name']" :breadcrumbs="[
                {caption: 'Причалы', to: {name: 'pier-list'}},
            ]">
                <actions-menu>
                    <span @click="deleteUser">Удалить причал</span>
                </actions-menu>
            </page-title-bar>
        </template>

        <base-table-container>
            <base-table :borders="false" :highlight="false" :hover="false" :small="true">
                <base-table-row>
                    <base-table-cell :w="'200'">Название</base-table-cell>
                    <base-table-cell :bold="true" :size="'lg'">{{ data.data.name }}</base-table-cell>
                </base-table-row>
                <base-table-row>
                    <base-table-cell :w="'200'">Статус</base-table-cell>
                    <base-table-cell :bold="true" :size="'lg'">{{ data.data.status }}</base-table-cell>
                </base-table-row>
            </base-table>
        </base-table-container>

        <container :no-bottom="true">
            <base-link-button :to="{ name: 'pier-edit', params: { id: pierId }}">Редактировать
            </base-link-button>
        </container>

    </page>
</template>

<script>
import genericDataSource from "../../../../Helpers/Core/genericDataSource";

import Page from "../../../../Layouts/Page";
import BaseButton from "../../../../Components/Base/BaseButton";
import UseBaseTableBundle from "../../../../Mixins/UseBaseTableBundle";
import Container from "../../../../Layouts/Parts/Container";
import BaseLinkButton from "../../../../Components/Base/BaseLinkButton";
import PageTitleBar from "../../../../Layouts/Parts/PageTitleBar";
import ActionsMenu from "../../../../Components/ActionsMenu";

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
        deleting: false,
    }),

    computed: {
        pierId() {
            return this.$route.params.id;
        },

        processing() {
            return this.deleting || this.data.loading;
        },
    },

    created() {
        this.data = genericDataSource('/api/piers/view');
        this.data.load({id: this.pierId});
    },

    methods: {
        deleteUser() {
            const name = this.data.data['name'];
            this.$dialog.show('Удалить причал "' + name + '"?', 'question', 'red', [
                this.$dialog.button('no', 'Отмена', 'blue'),
                this.$dialog.button('yes', 'Продолжить', 'red'),
            ]).then(result => {
                if (result === 'yes') {
                    // delete logic
                    // this.deleting = true;
                    // axios.post('/api/users/staff/delete', {id: this.userId})
                    //     .then(response => {
                    this.$dialog.show('Причал удалён', 'success', 'green', [
                        this.$dialog.button('ok', 'OK', 'blue')
                    ], 'center')
                        .finally(() => {
                            // this.$router.push({name: 'staff-user-list'});
                            console.log('dialog closed');
                        });
                    // })
                    // .catch(error => {
                    //     this.$dialog.show(error.data.message, 'error', 'red', [
                    //         this.$dialog.button('ok', 'OK', 'blue')
                    //     ], 'center')
                    // })
                    // .finally(() => {
                    //     this.deleting = false;
                    // });
                }
            });
        }
    }
}
</script>
