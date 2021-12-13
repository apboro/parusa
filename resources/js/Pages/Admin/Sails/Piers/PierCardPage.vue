<template>
    <page :loading="processing">
        <template v-slot:header>
            <page-title-bar :title="data.data['name']" :breadcrumbs="[
                {caption: 'Причалы', to: {name: 'pier-list'}},
            ]">
                <actions-menu>
                    <span @click="deletePier">Удалить причал</span>
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
import DeleteEntry from "../../../../Mixins/DeleteEntry";

export default {
    components: {
        ActionsMenu,
        PageTitleBar,
        Page,
        BaseButton,
        Container,
        BaseLinkButton,
    },

    mixins: [UseBaseTableBundle, DeleteEntry],

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
        deletePier() {
            const name = this.data.data['name'];

            this.deleteEntry('Удалить причал "' + name + '"?', '/api/piers/delete', {id: this.pierId})
                .then(() => {
                    this.$router.push({name: 'pier-list'});
                });
        },
    }
}
</script>
