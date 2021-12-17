<template>
    <page :loading="processing">
        <template v-slot:header>
            <page-title-bar :title="title" :breadcrumbs="[
                {caption: 'Причалы', to: {name: 'pier-list'}},
            ]">
                <actions-menu>
                    <span @click="deletePier">Удалить причал</span>
                </actions-menu>
            </page-title-bar>
        </template>

        <layout-routed-tabs
            :tabs="{
                description: 'Описание причала',
                schedule: 'Расписание рейсов',
            }"
            @change="tab = $event"
        />

        <keep-alive>
            <pier-info v-if="tab === 'description'"
                       :pier-id="pierId"
                       :datasource="data"
            />
        </keep-alive>
        <message v-if="tab === 'schedule'">Здесь будет расписание рейсов для данного причала</message>

    </page>
</template>

<script>
import Page from "../../../../Layouts/Page";
import PageTitleBar from "../../../../Layouts/Parts/PageTitleBar";
import ActionsMenu from "../../../../Components/ActionsMenu";
import LayoutRoutedTabs from "../../../../Components/Layout/LayoutRoutedTabs";
import PierInfo from "../../../../Parts/Sails/Piers/PierInfo";
import DeleteEntry from "../../../../Mixins/DeleteEntry";
import genericDataSource from "../../../../Helpers/Core/genericDataSource";
import Message from "../../../../Layouts/Parts/Message";

export default {
    components: {
        PierInfo,
        LayoutRoutedTabs,
        ActionsMenu,
        PageTitleBar,
        Page,
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
