<template>
    <LayoutPage :loading="processing" :title="data.data['name']"
                :breadcrumbs="[{caption: 'Промоутеры', to: {name: 'promoters-list'}}]"
                :link="{name: 'promoters-list'}"
                :link-title="'К списку промоутеров'"
    >
        <template #actions>
            <GuiActionsMenu>
                <span class="link" @click="deletePartner">Удалить промоутера</span>
            </GuiActionsMenu>
        </template>

        <template v-if="data.is_loaded">
            <LayoutRoutedTabs :tabs="{
                details: 'Персональные данные',
                access: 'Доступ',
            }" @change="tab = $event"/>

            <PromoterInfo v-if="tab === 'details'" :data="data.data" :partner-id="partnerId" :editable="true" @update="update"/>
<!--            <RepresentativeAccess v-if="tab === 'access'" :representative-id="representativeId" :data="data.data" :editable="true" @update="update"/>-->

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
import RepresentativeAccess from "@/Pages/Admin/Representatives/Parts/RepresentativeAccess.vue";
import PromoterInfo from "@/Pages/Admin/Promoters/Parts/PromoterInfo.vue";

export default {
    components: {
        PromoterInfo,
        RepresentativeAccess,
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
        data: data('/api/promoters/view'),
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

            this.deleteEntry('Удалить карточку партнёра "' + name + '"?', '/api/promoters/delete', {id: this.partnerId})
                .then(() => {
                    this.$router.push({name: 'promoters-list'});
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
