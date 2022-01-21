<template>
    <container mt-20 mb-20>
        <message>Здесь будет баланс лицевого счёта</message>
    </container>
    <page :loading="processing">
        <template v-slot:header>
            <page-title-bar
                :title="'Карточка партнёра ' + data.data['name']"
            >
            </page-title-bar>
        </template>

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
import PageTitleBar from "../../Layouts/Parts/PageTitleBar";
import PartnerInfo from "../../Parts/Partners/Partner/PartnerInfo";
import PartnerRepresentatives from "../../Parts/Partners/Partner/PartnerRepresentatives";
import Message from "@/Components/GUI/Message";
import Container from "../../Components/GUI/Container";
import Heading from "../../Components/GUI/Heading";

export default {
    components: {
        Heading,
        Container,
        Page,
        PageTitleBar,
        PartnerInfo,
        PartnerRepresentatives,
        Message,
    },

    data: () => ({
        data: null,
    }),

    computed: {
        partnerId() {
            return Number(this.$route.params.id);
        },

        processing() {
            return this.data.loading;
        },
    },

    created() {
        this.data = genericDataSource('/api/partner/partners/view');
        this.data.load({id: this.partnerId});
    },
}
</script>
