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
                    <span @click="deletePartner">Удалить партнёра</span>
                </actions-menu>
            </page-title-bar>
        </template>

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
        <message v-if="tab === 'account'">Здесь будет лицевой счёт</message>
        <message v-if="tab === 'rates'">Здесь будет тарифы</message>
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
            <order-registry v-if="sub_tab === 'sales'"/>
            <tickets-registry v-if="sub_tab === 'tickets'"/>
            <reserves-registry v-if="sub_tab === 'reserves'"/>
        </container>
    </page>
</template>

<script>
import genericDataSource from "../../../../Helpers/Core/genericDataSource";
import DeleteEntry from "../../../../Mixins/DeleteEntry";

import Page from "../../../../Layouts/Page";
import PageTitleBar from "../../../../Layouts/Parts/PageTitleBar";
import ActionsMenu from "../../../../Components/ActionsMenu";
import LayoutRoutedTabs from "../../../../Components/Layout/LayoutRoutedTabs";
import PartnerInfo from "../../../../Parts/Partners/Partner/PartnerInfo";
import PartnerRepresentatives from "../../../../Parts/Partners/Partner/PartnerRepresentatives";
import Message from "../../../../Layouts/Parts/Message";
import OrderRegistry from "../../../../Parts/Registries/OrderRegistry";
import Container from "../../../../Components/GUI/Container";
import TicketsRegistry from "../../../../Parts/Registries/TicketsRegistry";
import ReservesRegistry from "../../../../Parts/Registries/ReservesRegistry";
import LayoutTabs from "../../../../Components/Layout/LayoutTabs";

export default {
    components: {
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
        Message,
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
