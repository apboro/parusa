<template>
    <LayoutPage :loading="processing" :title="data.data['name']"
                :breadcrumbs="[{caption: 'Партнёры', to: {name: 'partners-list'}}]"
                :link="{name: 'partners-list'}"
                :link-title="'К списку партнёров'"
    >
        <template #actions>
            <GuiActionsMenu>
                <span class="link" @click="deletePartner">Удалить партнёра</span>
            </GuiActionsMenu>
        </template>

        <template v-if="data.is_loaded">
            <LayoutRoutedTabs :tabs="{
                details: 'Карточка партнёра',
                representatives: 'Представители',
                account: 'Лицевой счёт',
                rates: 'Тарифы',
                sale_registry: 'Реестр продаж',
            }" @change="tab = $event"/>

            <PartnerInfo v-if="tab === 'details'" :data="data.data" :partner-id="partnerId" :editable="true" @update="update"/>
            <PartnerRepresentatives v-if="tab === 'representatives'" :data="data.data" :partner-id="partnerId" :editable="true" @update="update"/>
            <!--            <partner-account v-if="tab === 'account'" :partner-id="partnerId" :editable="true"/>-->
            <!--            <partner-ticket-rates v-if="tab === 'rates'" :partner-id="partnerId" :editable="true"/>-->
            <GuiContainer v-if="tab === 'sale_registry'">
                <GuiTabs :initial="'sales'" :tabs="{sales: 'Реестр заказов',tickets: 'Реестр билетов',reserves: 'Реестр броней'}" @change="sub_tab = $event"/>
                <!--                <AdminOrderRegistry v-if="sub_tab === 'sales'" :partner-id="partnerId"/>-->
                <!--                <tickets-registry v-if="sub_tab === 'tickets'" :partner-id="partnerId"/>-->
                <!--                <reserves-registry v-if="sub_tab === 'reserves'" :partner-id="partnerId"/>-->
            </GuiContainer>
        </template>
    </LayoutPage>
</template>

<script>
import DeleteEntry from "../../../Mixins/DeleteEntry";
import data from "@/Core/Data";

import AdminOrderRegistry from "@/Pages/Admin/Registries/Parts/AdminOrderRegistry";
import LayoutPage from "@/Components/Layout/LayoutPage";
import GuiActionsMenu from "@/Components/GUI/GuiActionsMenu";
import LayoutRoutedTabs from "@/Components/Layout/LayoutRoutedTabs";
import GuiContainer from "@/Components/GUI/GuiContainer";
import GuiTabs from "@/Components/GUI/GuiTabs";
import PartnerInfo from "@/Pages/Admin/Partners/Parts/PartnerInfo";
import PartnerRepresentatives from "@/Pages/Admin/Partners/Parts/PartnerRepresentatives";

export default {
    components: {
        PartnerRepresentatives,
        PartnerInfo,
        GuiTabs,
        GuiContainer,
        LayoutRoutedTabs,
        GuiActionsMenu,
        LayoutPage,


        AdminOrderRegistry,
    },

    mixins: [DeleteEntry],

    data: () => ({
        tab: null,
        sub_tab: null,
        data: data('/api/partners/view'),
    }),

    computed: {
        partnerId() {
            return Number(this.$route.params.id);
        },

        processing() {
            return this.deleting || this.data.is_loading;
        },
    },

    created() {
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

        update(payload) {
            Object.keys(payload).map(key => {
                this.data.data[key] = payload[key];
            })
        }
    }
}
</script>
