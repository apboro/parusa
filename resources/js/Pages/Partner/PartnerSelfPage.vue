<template>
    <page :loading="processing" :title="$route.meta['title'] + ' ' + data.data['name']">
        <template v-if="data.loaded">
            <partner-info :datasource="data" :partner-id="partnerId" :editable="false"/>
            <heading mt-30 mb-30 bold>Представители</heading>
            <partner-representatives :datasource="data" :partner-id="partnerId" :editable="false" :links="false"/>
        </template>
    </page>
</template>

<script>
import genericDataSource from "../../Helpers/Core/genericDataSource";

import Page from "../../Layouts/Page";
import PartnerInfo from "../../Parts/Partners/Partner/PartnerInfo";
import PartnerRepresentatives from "../../Parts/Partners/Partner/PartnerRepresentatives";
import Heading from "../../Components/GUI/GuiHeading";

export default {
    components: {
        Page,
        PartnerInfo,
        PartnerRepresentatives,
        Heading,
    },

    data: () => ({
        data: null,
    }),

    computed: {
        partnerId() {
            return Number(this.$route.params.id);
        },

        processing() {
            return Boolean(this.data.loading);
        },
    },

    created() {
        this.data = genericDataSource('/api/partners/partner/view');
        this.data.load({id: this.partnerId});
    },
}
</script>
