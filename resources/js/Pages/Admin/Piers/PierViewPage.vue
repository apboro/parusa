<template>
    <LayoutPage :loading="processing" :title="title"
                :breadcrumbs="[{caption: 'Причалы', to: {name: 'pier-list'}}]"
                :link="{name: 'pier-list'}"
                :link-title="'К списку причалов'"
    >
        <template v-slot:actions>
            <GuiActionsMenu>
                <span class="link" @click="deletePier">Удалить причал</span>
            </GuiActionsMenu>
        </template>

        <LayoutRoutedTabs :tabs="{description: 'Описание причала', schedule: 'Расписание рейсов'}" @change="tab = $event"/>

        <PierInfo v-if="tab === 'description'" :data="data.data" :pier-id="pierId" :editable="true" @update="update"/>

        <GuiHeading v-if="tab === 'schedule' && trips_title !== null" mt-15>{{ trips_title }}</GuiHeading>
        <TripsList v-if="tab === 'schedule'" :pier-id="pierId" @setTitle="trips_title = $event"/>
    </LayoutPage>
</template>

<script>
import data from "@/Core/Data";
import DeleteEntry from "@/Mixins/DeleteEntry";
import LayoutPage from "@/Components/Layout/LayoutPage";
import GuiActionsMenu from "@/Components/GUI/GuiActionsMenu";
import LayoutRoutedTabs from "@/Components/Layout/LayoutRoutedTabs";
import GuiHeading from "@/Components/GUI/GuiHeading";
import TripsList from "@/Pages/Admin/Trips/Parts/TripsList";
import PierInfo from "@/Pages/Admin/Piers/Parts/PierInfo";

export default {
    components: {
        LayoutPage,
        GuiActionsMenu,
        LayoutRoutedTabs,
        GuiHeading,
        PierInfo,
        TripsList,
    },

    mixins: [DeleteEntry],

    data: () => ({
        data: data('/api/piers/view'),
        tab: null,
        trips_title: null,
    }),

    computed: {
        pierId() {
            return Number(this.$route.params.id);
        },
        processing() {
            return this.deleting || this.data.is_loading;
        },
        title() {
            return this.data.is_loaded ? this.data.data['name'] : '...';
        }
    },

    created() {
        this.data.load({id: this.pierId})
            .catch(response => response.code === 404 && this.$router.replace({name: '404'}));
    },

    methods: {
        deletePier() {
            const name = this.data.data['name'];
            this.deleteEntry('Удалить причал "' + name + '"?', '/api/piers/delete', {id: this.pierId})
                .then(() => {
                    this.$router.push({name: 'pier-list'});
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
