<template>
    <LayoutPage :loading="processing" :title="data.data['name']"
                :breadcrumbs="[{caption: 'Промоутеры', to: {name: 'terminal-promoters'}}]"
                :link="{name: 'terminal-promoters'}"
                :link-title="'К списку промоутеров'"
    >

        <template v-if="data.is_loaded">
            <LayoutRoutedTabs :tabs="{
                details: 'Персональные данные',
                access: 'Лицевой счёт',
                registry: 'Реестр продаж',
            }" @change="tab = $event"/>

            <PromoterInfo v-if="tab === 'details'" :data="data.data" :partner-id="partnerId" :editable="true"/>
            <PromoterAccount v-if="tab === 'access'" :partnerId="partnerId"/>
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
import PromoterInfo from "@/Pages/Terminal/Parts/PromoterInfo.vue";
import PromoterAccount from "@/Pages/Terminal/Parts/PromoterAccount.vue";

export default {
    components: {
        PromoterAccount,
        PromoterInfo,
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
    }
}
</script>
