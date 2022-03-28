<template>
    <LayoutPage :loading="data.is_loading" :title="$route.meta['title'] + ' ' + data.data['name']">
        <template v-if="data.is_loaded">
            <PartnerInfo :data="data.data"/>
            <GuiHeading mt-30 mb-30 bold>Представители</GuiHeading>
            <PartnerRepresentatives :positions="data.data['positions']"/>
        </template>
    </LayoutPage>
</template>

<script>
import data from "@/Core/Data";
import LayoutPage from "@/Components/Layout/LayoutPage";
import GuiHeading from "@/Components/GUI/GuiHeading";
import PartnerInfo from "@/Pages/Partner/Parts/PartnerInfo";
import PartnerRepresentatives from "@/Pages/Partner/Parts/PartnerRepresentatives";

export default {
    components: {
        PartnerRepresentatives,
        PartnerInfo,
        GuiHeading,
        LayoutPage

    },

    data: () => ({
        data: data('/api/partners/details'),
    }),

    created() {
        this.data.load();
    },
}
</script>
