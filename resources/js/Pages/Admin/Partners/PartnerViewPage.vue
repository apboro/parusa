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
                qr_code: 'QR-Коды',
            }" @change="tab = $event"/>

            <PartnerInfo v-if="tab === 'details'" :data="data.data" :partner-id="partnerId" :editable="true" @update="update"/>
            <PartnerRepresentatives v-if="tab === 'representatives'" :data="data.data" :partner-id="partnerId" :editable="true" @update="update"/>
            <PartnerAccount v-if="tab === 'account'" :partner-id="partnerId" :editable="true"/>
            <PartnerTicketRates v-if="tab === 'rates'" :partner-id="partnerId" :editable="true"/>
            <GuiContainer v-if="tab === 'sale_registry'">
                <GuiTabs :initial="'sales'" :tabs="{sales: 'Реестр заказов',tickets: 'Реестр билетов',reserves: 'Реестр броней'}" @change="sub_tab = $event"/>
                <AdminOrderRegistry v-if="sub_tab === 'sales'" :partner-id="partnerId"/>
                <AdminTicketsRegistry v-if="sub_tab === 'tickets'" :partner-id="partnerId"/>
                <AdminReservesRegistry v-if="sub_tab === 'reserves'" :partner-id="partnerId"/>
            </GuiContainer>
            <PartnerQrCodes v-if="tab === 'qr_code'" :partner-id="partnerId"/>
        </template>
    </LayoutPage>
</template>

<script>
import data from "@/Core/Data";
import DeleteEntry from "@/Mixins/DeleteEntry";
import AdminOrderRegistry from "@/Pages/Admin/Registries/Parts/AdminOrderRegistry";
import LayoutPage from "@/Components/Layout/LayoutPage";
import GuiActionsMenu from "@/Components/GUI/GuiActionsMenu";
import LayoutRoutedTabs from "@/Components/Layout/LayoutRoutedTabs";
import GuiContainer from "@/Components/GUI/GuiContainer";
import GuiTabs from "@/Components/GUI/GuiTabs";
import PartnerInfo from "@/Pages/Admin/Partners/Parts/PartnerInfo";
import PartnerRepresentatives from "@/Pages/Admin/Partners/Parts/PartnerRepresentatives";
import PartnerAccount from "@/Pages/Admin/Partners/Parts/PartnerAccount";
import AdminTicketsRegistry from "@/Pages/Admin/Registries/Parts/AdminTicketsRegistry";
import AdminReservesRegistry from "@/Pages/Admin/Registries/Parts/AdminReservesRegistry";
import PartnerTicketRates from "@/Pages/Admin/Partners/Parts/PartnerTicketRates";
import PartnerQrCodes from "@/Pages/Admin/Partners/Parts/PartnerQrCodes.vue";

export default {
    components: {
        PartnerQrCodes,
        PartnerTicketRates,
        AdminReservesRegistry,
        AdminTicketsRegistry,
        PartnerAccount,
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
