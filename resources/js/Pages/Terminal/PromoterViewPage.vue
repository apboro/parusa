<template>
    <LayoutPage :loading="processing" :title="data.data['name']"
                :breadcrumbs="[{caption: 'Промоутеры', to: {name: 'terminal-promoters'}}]"
                :link="{name: 'terminal-promoters'}"
                :link-title="'К списку промоутеров'"
    >

        <template v-if="data.is_loaded && partnerId">
            <LayoutRoutedTabs :tabs="{
                details: 'Персональные данные',
                account: 'Лицевой счёт',
                registry: 'Реестр продаж',
            }" @change="tab = $event"/>

            <PromoterInfo v-if="tab === 'details'" :data="data.data" :partnerId="partnerId" :editable="true"/>
            <PromoterAccount v-if="tab === 'account'" :partnerId="partnerId"/>
            <AdminOrderRegistry v-if="tab === 'registry'" :partnerId="partnerId"/>
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
import PromoterInfo from "@/Pages/Terminal/Parts/PromoterInfo.vue";
import PromoterAccount from "@/Pages/Terminal/Parts/PromoterAccount.vue";

export default {
    components: {
        PromoterAccount,
        PromoterInfo,
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
