<template>
    <LayoutPage :loading="processing" :title="title"
                :breadcrumbs="[{caption: 'Рейсы', to: {name: 'trip-list'}}]"
                :link="{name: 'trip-list'}"
                :link-title="'К списку рейсов'"
    >
        <template v-if="accepted" v-slot:actions>
            <GuiActionsMenu>
                <span class="link" @click="deleteTrip">Удалить рейс</span>
            </GuiActionsMenu>
        </template>

        <LayoutRoutedTabs :tabs="{description: 'Описание рейса', tickets: 'Проданные билеты'}" @change="tab = $event"/>

        <TripInfo v-if="tab === 'description'" :trip-id="tripId" :data="data.data" :editable="true" @update="update"/>
        <AdminTicketsRegistry v-if="tab === 'tickets'" :trip-id="tripId"/>

    </LayoutPage>
</template>

<script>
import data from "@/Core/Data";
import LayoutPage from "@/Components/Layout/LayoutPage";
import GuiActionsMenu from "@/Components/GUI/GuiActionsMenu";
import LayoutRoutedTabs from "@/Components/Layout/LayoutRoutedTabs";
import TripInfo from "@/Pages/Admin/Trips/Parts/TripInfo";
import DeleteEntry from "@/Mixins/DeleteEntry";
import AdminTicketsRegistry from "@/Pages/Admin/Registries/Parts/AdminTicketsRegistry";
import roles from "@/Mixins/roles.vue";

export default {
    components: {
        AdminTicketsRegistry,
        LayoutPage,
        GuiActionsMenu,
        LayoutRoutedTabs,
        TripInfo,
    },

    mixins: [roles, DeleteEntry],

    data: () => ({
        data: data('/api/trips/view'),
        tab: null,
    }),

    computed: {
        tripId() {
            return Number(this.$route.params.id);
        },
        processing() {
            return this.deleting || this.data.loading;
        },
        title() {
            return this.data.is_loaded ? String(this.data.data['name']) : 'Рейс №...';
        },
        accepted() {
            return this.hasRole(['admin', 'office_manager']);
        },
    },

    created() {
        this.data.load({id: this.tripId})
            .catch(response => response.code === 404 && this.$router.replace({name: '404'}));
    },

    methods: {
        deleteTrip() {
            this.deleteEntry('Удалить рейс №' + this.data.data['id'] + '?', '/api/trips/delete', {id: this.tripId, mode: 'single'})
                .then(() => this.$router.push({name: 'trip-list'}));
        },
        update(payload) {
            Object.keys(payload).map(key => {
                this.data.data[key] = payload[key];
            })
        }
    }
}
</script>
