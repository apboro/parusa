<template>
    <page :loading="processing">
        <template v-slot:header>
            <page-title-bar :title="title" :breadcrumbs="[{caption: 'Рейсы', to: {name: 'trip-list'}}]">
                <actions-menu>
                    <span @click="deleteTrip">Удалить рейс</span>
                </actions-menu>
            </page-title-bar>
        </template>

        <layout-routed-tabs :tabs="{description: 'Описание рейса', tickets: 'Проданные билеты'}" @change="tab = $event"/>

        <trip-info v-if="tab === 'description'" :datasource="data" :trip-id="tripId" :editable="true"/>
        <message v-if="tab === 'tickets'">Здесь будут проданные билеты</message>

    </page>
</template>

<script>
import genericDataSource from "../../../../Helpers/Core/genericDataSource";
import DeleteEntry from "../../../../Mixins/DeleteEntry";

import Page from "../../../../Layouts/Page";
import PageTitleBar from "../../../../Layouts/Parts/PageTitleBar";
import ActionsMenu from "../../../../Components/ActionsMenu";
import LayoutRoutedTabs from "../../../../Components/Layout/LayoutRoutedTabs";
import Message from "../../../../Layouts/Parts/Message";
import TripInfo from "../../../../Parts/Sails/Trips/TripInfo";

export default {
    components: {
        TripInfo,
        Page,
        PageTitleBar,
        ActionsMenu,
        LayoutRoutedTabs,
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
        tripId() {
            return Number(this.$route.params.id);
        },
        processing() {
            return this.deleting || this.data.loading;
        },
    },

    created() {
        this.data = genericDataSource('/api/trips/view');
        this.data.onLoad = this.loaded;
        this.data.load({id: this.tripId});
    },

    methods: {
        loaded(data) {
            this.title = data['name'];
        },
        deleteTrip() {
            const name = this.data.data['id'];
            this.deleteEntry('Удалить рейс №' + name + '?', '/api/trips/delete', {id: this.tripId})
                .then(() => {
                    this.$router.push({name: 'trip-list'});
                });
        },
    }
}
</script>
