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
import PartnerInfo from "../../../../Parts/Partners/Partner/PartnerInfo";
import PartnerRepresentatives from "../../../../Parts/Partners/Partner/PartnerRepresentatives";
import Message from "../../../../Layouts/Parts/Message";

export default {
    components: {
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
