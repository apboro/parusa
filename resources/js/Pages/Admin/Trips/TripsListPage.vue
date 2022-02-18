<template>
    <LayoutPage :title="titleProxy">
        <template #actions>
            <GuiActionsMenu>
                <router-link class="link" :to="{ name: 'trip-edit', params: { id: 0 }, query: linkQuery}">Добавить рейс</router-link>
            </GuiActionsMenu>
        </template>

        <TripsList @setTitle="title = $event" @setStartPier="start_pier_id = $event"/>
    </LayoutPage>
</template>

<script>
import LayoutPage from "@/Components/Layout/LayoutPage";
import TripsList from "@/Pages/Admin/Trips/Parts/TripsList";
import GuiActionsMenu from "@/Components/GUI/GuiActionsMenu";

export default {
    components: {
        GuiActionsMenu,
        TripsList,
        LayoutPage
    },

    computed: {
        titleProxy() {
            return String(this.title !== null ? this.title : this.$route.meta['title']);
        },
        linkQuery() {
            let query = {};
            if (this.start_pier_id !== null) {
                query['pier'] = this.start_pier_id;
            }
            /**
             * For future use:
             * if (this.list.filters['excursion_id'] !== null) {
             *   query['excursion'] = this.list.filters['excursion_id'];
             * }
             */
            return query;
        },
    },

    data: () => ({
        title: null,
        start_pier_id: null,
    }),
}
</script>
