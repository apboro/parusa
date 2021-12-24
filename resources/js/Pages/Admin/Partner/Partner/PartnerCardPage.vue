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

        <partenr-info v-if="tab === 'details'"
                      :partner-id="partnerId"
                      :editable="true"
                      :datasource="data"
        />
        <partner-representatives v-if="tab === 'representatives'"
                                 :partner-id="partnerId"
                                 :editable="true"
                                 :datasource="data"
        />
        <message v-if="tab === 'account'">Здесь будет лицевой счёт</message>
        <message v-if="tab === 'rates'">Здесь будет тарифы</message>
        <message v-if="tab === 'sale_registry'">Здесь будет реестр продаж</message>

    </page>
</template>

<script>
import genericDataSource from "../../../../Helpers/Core/genericDataSource";

import DeleteEntry from "../../../../Mixins/DeleteEntry";
import Page from "../../../../Layouts/Page";
import PageTitleBar from "../../../../Layouts/Parts/PageTitleBar";
import ActionsMenu from "../../../../Components/ActionsMenu";
import LayoutRoutedTabs from "../../../../Components/Layout/LayoutRoutedTabs";
import Message from "../../../../Layouts/Parts/Message";
import PartenrInfo from "../../../../Parts/Partners/Partner/PartenrInfo";
import PartnerRepresentatives from "../../../../Parts/Partners/Partner/PartnerRepresentatives";

export default {
    components: {
        PartnerRepresentatives,
        PartenrInfo,
        Message,
        LayoutRoutedTabs,
        ActionsMenu,
        PageTitleBar,
        Page

    },

    mixins: [DeleteEntry],

    data: () => ({
        tab: null,
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
