<template>
    <page :loading="processing">
        <template v-slot:header>
            <page-title-bar
                :title="data.data['name']"
                :breadcrumbs="[{caption: 'Партнёры', to: {name: 'partners-list'}}]"
                :link="{name: 'partners-list'}"
                :link-title="'К списку партнёров'"
            >
                <actions-menu>
                    <span class="link" @click="deletePartner">Удалить партнёра</span>
                </actions-menu>
            </page-title-bar>
        </template>

        <template v-if="data.loaded">
            <layout-routed-tabs
                :tabs="{
                details: 'Карточка партнёра',
                representatives: 'Представители',
                account: 'Лицевой счёт',
                rates: 'Тарифы',
                sale_registry: 'Реестр продаж',
            }"
                @change="tab = $event"
            />

            <partner-info v-if="tab === 'details'" :datasource="data" :partner-id="partnerId" :editable="true"/>
            <partner-representatives v-if="tab === 'representatives'" :datasource="data" :partner-id="partnerId" :editable="true"/>
            <partner-account v-if="tab === 'account'" :partner-id="partnerId" :editable="true"/>
            <partner-ticket-rates v-if="tab === 'rates'" :partner-id="partnerId" :editable="true"/>
            <container v-if="tab === 'sale_registry'">
                <layout-tabs
                    :initial="'sales'"
                    :tabs="{
                sales: 'Реестр заказов',
                tickets: 'Реестр билетов',
                reserves: 'Реестр броней',
            }"
                    @change="sub_tab = $event"
                />
                <order-registry v-if="sub_tab === 'sales'" :partner-id="partnerId"/>
                <tickets-registry v-if="sub_tab === 'tickets'" :partner-id="partnerId"/>
                <reserves-registry v-if="sub_tab === 'reserves'" :partner-id="partnerId"/>
            </container>
        </template>
    </page>
</template>

<script>
import genericDataSource from "../../../../Helpers/Core/genericDataSource";
import DeleteEntry from "../../../../Mixins/DeleteEntry";

import Page from "../../../../Layouts/Page";
import PageTitleBar from "../../../../Layouts/Parts/PageTitleBar";
import ActionsMenu from "../../../../Components/GUI/GuiActionsMenu";
import LayoutRoutedTabs from "../../../../Components/Layout/LayoutRoutedTabs";
import PartnerInfo from "../../../../Parts/Partners/Partner/PartnerInfo";
import PartnerRepresentatives from "../../../../Parts/Partners/Partner/PartnerRepresentatives";
import OrderRegistry from "../../Registries/Parts/OrderRegistry";
import Container from "../../../../Components/GUI/GuiContainer";
import TicketsRegistry from "../../Registries/Parts/TicketsRegistry";
import ReservesRegistry from "../../Registries/Parts/ReservesRegistry";
import LayoutTabs from "../../../../Components/GUI/GuiTabs";
import PartnerTicketRates from "../../../../Parts/Partners/Partner/PartnerTicketRates";
import PartnerAccount from "../../../../Parts/Partners/Partner/PartnerAccount";

export default {
    components: {
        PartnerAccount,
        PartnerTicketRates,
        LayoutTabs,
        ReservesRegistry,
        TicketsRegistry,
        Container,
        OrderRegistry,
        Page,
        PageTitleBar,
        ActionsMenu,
        LayoutRoutedTabs,
        PartnerInfo,
        PartnerRepresentatives,
    },

    mixins: [DeleteEntry],

    data: () => ({
        tab: null,
        sub_tab: null,
        data: null,
        deleting: false,
    }),

    computed: {
        partnerId() {
            return Number(this.$route.params.id);
        },

        processing() {
            return this.deleting || this.data.loading;
        },
    },

    created() {
        this.data = genericDataSource('/api/partners/view');
        this.data.load({id: this.partnerId});
    },

    methods: {
        deletePartner() {
            const name = this.data.data['name'];

            this.deleteEntry('Удалить карточку партнёра "' + name + '"?', '/api/partners/delete', {id: this.partnerId})
                .then(() => {
                    this.$router.push({name: 'partners-list'});
                });
        },
    }
}
</script>
