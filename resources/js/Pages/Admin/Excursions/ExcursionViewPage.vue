<template>
    <LayoutPage :loading="processing" :title="data.data['name']"
                :breadcrumbs="[{caption: 'Экскурсии', to: {name: 'excursion-list'}}]"
                :link="{name: 'excursion-list'}"
                :link-title="'К списку экскурсий'"
    >
        <template v-slot:actions>
            <GuiActionsMenu>
                <span class="link" @click="deleteExcursion">Удалить экскурсию</span>
            </GuiActionsMenu>
        </template>

        <LayoutRoutedTabs :tabs="{description: 'Описание экскурсии', rates: 'Тарифы на билеты', schedule: 'Расписание'}" @change="tab = $event"/>

        <ExcursionInfo v-if="tab === 'description'" :excursion-id="excursionId" :data="data.data" :editable="true" @update="update"/>

        <ExcursionRates v-if="tab === 'rates'" :excursion-id="excursionId" :editable="true"/>

        <GuiHeading v-if="tab === 'schedule' && trips_title !== null" mt-15>{{ trips_title }}</GuiHeading>
        <TripsList v-if="tab === 'schedule'" :excursion-id="excursionId" @setTitle="trips_title = $event"/>

    </LayoutPage>
</template>

<script>
import data from "@/Core/Data";
import DeleteEntry from "@/Mixins/DeleteEntry";

import LayoutPage from "@/Components/Layout/LayoutPage";
import GuiActionsMenu from "@/Components/GUI/GuiActionsMenu";
import LayoutRoutedTabs from "@/Components/Layout/LayoutRoutedTabs";

import ExcursionInfo from "@/Pages/Admin/Excursions/Parts/ExcursionInfo";
import GuiHeading from "@/Components/GUI/GuiHeading";
import TripsList from "@/Pages/Admin/Trips/Parts/TripsList";
import ExcursionRates from "@/Pages/Admin/Excursions/Parts/ExcursionRates";

export default {
    components: {
        ExcursionRates,
        GuiHeading,
        TripsList,
        GuiActionsMenu,
        LayoutPage,
        LayoutRoutedTabs,
        ExcursionInfo,
    },

    mixins: [DeleteEntry],

    data: () => ({
        tab: null,
        data: data('/api/excursions/view'),
        deleting: false,
        trips_title: null,
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
        this.data.load({id: this.excursionId});
    },

    methods: {
        deleteExcursion() {
            this.deleteEntry(`Удалить экскурсию "${this.data.data['name']}"?`, '/api/excursions/delete', {id: this.excursionId})
                .then(() => {
                    this.$router.push({name: 'excursion-list'});
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
