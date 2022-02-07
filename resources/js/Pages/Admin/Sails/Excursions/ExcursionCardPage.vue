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
                    <span class="link" @click="deleteExcursion">Удалить экскурсию</span>
                </actions-menu>
            </page-title-bar>
        </template>

        <template v-if="data.loaded">
            <layout-routed-tabs :tabs="{description: 'Описание экскурсии',rates: 'Тарифы на билеты',schedule: 'Расписание'}" @change="tab = $event"/>

            <excursion-info v-if="tab === 'description'" :excursion-id="excursionId" :datasource="data" :editable="true"/>
            <excursion-ticket-rates v-if="tab === 'rates'" :excursion-id="excursionId" :editable="true"/>
            <message v-if="tab === 'schedule'">Здесь будет расписание рейсов для данной экскурсии</message>
        </template>
    </page>
</template>

<script>
import genericDataSource from "../../../../Helpers/Core/genericDataSource";
import DeleteEntry from "../../../../Mixins/DeleteEntry";

import Page from "../../../../Layouts/Page";
import PageTitleBar from "../../../../Layouts/Parts/PageTitleBar";
import ActionsMenu from "../../../../Components/GUI/GuiActionsMenu";
import LayoutRoutedTabs from "../../../../Components/Layout/LayoutRoutedTabs";
import ExcursionInfo from "../../../../Parts/Sails/Excursions/ExcursionInfo";
import Message from "@/Components/GUI/GuiMessage";
import ExcursionTicketRates from "../../../../Parts/Sails/Excursions/ExcursionTicketRates";

export default {
    components: {
        ExcursionTicketRates,
        Page,
        PageTitleBar,
        ActionsMenu,
        LayoutRoutedTabs,
        ExcursionInfo,
        Message,
    },

    mixins: [DeleteEntry],

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
