<template>
    <LayoutPage :loading="processing" :title="title"
                :breadcrumbs="[{caption: 'Теплоходы', to: {name: 'ship-list'}}]"
                :link="{name: 'ship-list'}"
                :link-title="'К списку теплоходов'"
    >
        <template v-if="accepted" v-slot:actions>
            <GuiActionsMenu>
                <span class="link" @click="deleteStaff">Удалить теплоход</span>
            </GuiActionsMenu>
        </template>


        <LayoutRoutedTabs :tabs="tabs" @change="tab = $event"/>

        <ShipInfo v-if="tab === 'info'" :data="data.data" :ship-id="shipId" :editable="accepted" @update="update"/>
        <SeatPlacement v-if="tab === 'seats_scheme'" :data="data.data" :ship-id="shipId" :editable="accepted"
                       @update="update"/>

    </LayoutPage>
</template>

<script>
import data from "@/Core/Data";
import DeleteEntry from "@/Mixins/DeleteEntry";
import LayoutPage from "@/Components/Layout/LayoutPage";
import GuiActionsMenu from "@/Components/GUI/GuiActionsMenu";
import LayoutRoutedTabs from "@/Components/Layout/LayoutRoutedTabs";
import ShipInfo from "@/Pages/Admin/Ships/Parts/ShipInfo.vue";
import roles from "@/Mixins/roles.vue";
import SeatPlacement from "@/Pages/Admin/Ships/Parts/SeatPlacement.vue";

export default {
    components: {
        SeatPlacement,
        ShipInfo,
        LayoutPage,
        GuiActionsMenu,
        LayoutRoutedTabs,
    },

    mixins: [roles, DeleteEntry],


    data: () => ({
        data: data('/api/ship/view'),
        tab: null,
    }),

    computed: {
        shipId() {
            return Number(this.$route.params.id);
        },
        processing() {
            return this.deleting || this.data.is_loading;
        },
        title() {
            return this.data.is_loaded ? this.data.data['name'] : '...';
        },
        accepted() {
            return this.hasRole(['admin']);
        },
        tabs() {
            if (this.data.data.ship_has_seats_scheme)
                return {info: 'Информация', seats_scheme: 'Схема рассадки'};
            else
                return {info: 'Информация'};
        }
    },

    created() {
        this.data.load({id: this.shipId})
            .catch(response => response.code === 404 && this.$router.replace({name: '404'}));
    },

    methods: {
        deleteStaff() {
            const name = this.data.data['name'];
            this.deleteEntry('Удалить теплоход "' + name + '"?', '/api/ship/delete', {id: this.shipId})
                .then(() => {
                    this.$router.push({name: 'ship-list'});
                });
        },
        update(payload) {
            Object.keys(payload).map(key => {
                this.data.data[key] = payload[key];
            })
        },
    }
}
</script>
