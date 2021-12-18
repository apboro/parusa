<template>
    <page :loading="processing">
        <template v-slot:header>
            <page-title-bar
                :title="data.data['name']"
                :breadcrumbs="[{caption: 'Каталог экскурсий', to: {name: 'excursion-list'}}]"
                :link="{name: 'excursion-list'}"
                :link-title="'К списку экскурсий'"
            >
                <actions-menu>
                    <span @click="deleteExcursion">Удалить экскурсию</span>
                </actions-menu>
            </page-title-bar>
        </template>

        <layout-routed-tabs
            :tabs="{
                description: 'Описание экскурсии',
                rates: 'Тарифы на билеты',
                schedule: 'Расписание',
            }"
            @change="tab = $event"
        />

        <keep-alive>
            <excursion-info v-if="tab === 'description'"
                            :excursion-id="excursionId"
                            :datasource="data"
                            :editable="true"
            />
        </keep-alive>
        <message v-if="tab === 'rates'">Здесь будут тарифы для данной экскурсии</message>
        <message v-if="tab === 'schedule'">Здесь будет расписание рейсов для данной экскурсии</message>

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
import LayoutRoutedTabs from "../../../../Components/Layout/LayoutRoutedTabs";
import ExcursionInfo from "../../../../Parts/Sails/Excursions/ExcursionInfo";
import Message from "../../../../Layouts/Parts/Message";

export default {
    components: {
        Message,
        ExcursionInfo,
        LayoutRoutedTabs,
        ActionsMenu,
        PageTitleBar,
        Page,
        BaseButton,
        Container,
        BaseLinkButton,
    },

    mixins: [UseBaseTableBundle, DeleteEntry],

    data: () => ({
        tab: null,
        data: null,
        deleting: false,
    }),

    computed: {
        excursionId() {
            return Number(this.$route.params.id);
        },

        processing() {
            return this.deleting || this.data.loading;
        },
    },

    created() {
        this.data = genericDataSource('/api/excursions/view');
        this.data.load({id: this.excursionId});
    },

    methods: {
        deleteExcursion() {
            const name = this.data.data['name'];

            this.deleteEntry('Удалить экскурсию "' + name + '"?', '/api/excursions/delete', {id: this.excursionId})
                .then(() => {
                    this.$router.push({name: 'excursion-list'});
                });
        },
    }
}
</script>
