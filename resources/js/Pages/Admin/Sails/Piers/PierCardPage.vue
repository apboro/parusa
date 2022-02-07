<template>
    <page :loading="processing">
        <template v-slot:header>
            <page-title-bar :title="title" :breadcrumbs="[{caption: 'Причалы', to: {name: 'pier-list'}}]">
                <actions-menu>
                    <span class="link" @click="deletePier">Удалить причал</span>
                </actions-menu>
            </page-title-bar>
        </template>

        <layout-routed-tabs :tabs="{description: 'Описание причала', schedule: 'Расписание рейсов'}" @change="tab = $event"/>

        <pier-info v-if="tab === 'description'" :datasource="data" :pier-id="pierId" :editable="true"/>
        <message v-if="tab === 'schedule'">Здесь будет расписание рейсов для данного причала</message>

    </page>
</template>

<script>
import genericDataSource from "../../../../Helpers/Core/genericDataSource";
import DeleteEntry from "../../../../Mixins/DeleteEntry";

import Page from "../../../../Layouts/Page";
import PageTitleBar from "../../../../Layouts/Parts/PageTitleBar";
import ActionsMenu from "../../../../Components/GUI/GuiActionsMenu";
import LayoutRoutedTabs from "../../../../Components/Layout/LayoutRoutedTabs";
import PierInfo from "../../../../Parts/Sails/Piers/PierInfo";
import Message from "@/Components/GUI/GuiMessage";

export default {
    components: {
        Page,
        PageTitleBar,
        ActionsMenu,
        LayoutRoutedTabs,
        PierInfo,
        Message,
    },

    mixins: [DeleteEntry],

    data: () => ({
        data: null,
        title: null,
        deleting: false,
        tab: null,
    }),

    computed: {
        pierId() {
            return Number(this.$route.params.id);
        },
        processing() {
            return this.deleting || this.data.loading;
        },
    },

    created() {
        this.data = genericDataSource('/api/piers/view');
        this.data.onLoad = this.loaded;
        this.data.load({id: this.pierId});
    },

    methods: {
        loaded(data) {
            this.title = data['name'];
        },
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
