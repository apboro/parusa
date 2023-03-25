<template>
    <LayoutPage :title="$route.meta['title']" :loading="list.is_loading">
        <LayoutFilters>
            <LayoutFiltersItem :title="'Период'">
                <InputDate
                    v-model="list.filters['date_from']"
                    :original="list.filters_original['date_from']"
                    @change="list.load()"
                />
                <InputDate
                    v-model="list.filters['date_to']"
                    :original="list.filters_original['date_to']"
                    @change="list.load()"
                />
            </LayoutFiltersItem>
            <LayoutFiltersItem :class="'w-25'" :title="'Партнер'">
                <DictionaryDropDown
                    :dictionary="'partners'"
                    :fresh="true"
                    v-model="list.filters['partner_id']"
                    :original="list.filters_original['partner_id']"
                    :placeholder="'Все'"
                    :has-null="true"
                    :search="true"
                    :right="true"
                    :small="true"
                    @change="list.load()"
                />
            </LayoutFiltersItem>
        </LayoutFilters>
        <ListTableResponsive v-if="list.list.length > 0" :titles="list.titles">
            <ListTableResponsiveRow v-for="qrcode in list.list">
                <ListTableResponsiveCell :mobile-title="list.titles[0]">
                    {{ qrcode.qr_code.name }}
                </ListTableResponsiveCell>
                <ListTableResponsiveCell :mobile-title="list.titles[1]">
                    {{ qrcode.qr_code.link }}
                </ListTableResponsiveCell>
                <ListTableResponsiveCell :mobile-title="list.titles[2]">
                    {{ qrcode.visits_count }}
                </ListTableResponsiveCell>
                <ListTableResponsiveCell :mobile-title="list.titles[3]">
                    {{ qrcode.payed_tickets_count }}
                </ListTableResponsiveCell>
                <ListTableResponsiveCell :mobile-title="list.titles[4]">
                    <span class="link" @click="generateQr(qrcode)">ПОСМОТРЕТЬ</span>
                </ListTableResponsiveCell>
            </ListTableResponsiveRow>
        </ListTableResponsive>


        <GuiMessage border v-else-if="list.is_loaded">Ничего не найдено</GuiMessage>
        <PopUp :close-on-overlay="true" ref="qr_popup">
            <img :src="qr_image" alt="qr" style="width: 300px"/>
        </PopUp>

        <Pagination :pagination="list.pagination" @pagination="(page, per_page) => list.load(page, per_page)"/>

    </LayoutPage>
</template>

<script>
import list from "@/Core/List";
import LayoutPage from "@/Components/Layout/LayoutPage.vue";
import LayoutRoutedTabs from "@/Components/Layout/LayoutRoutedTabs.vue";
import PartnerCodes from "@/Pages/Partner/Parts/PartnerCodes.vue";
import LoadingProgress from "@/Components/LoadingProgress.vue";
import GuiActionsMenu from "@/Components/GUI/GuiActionsMenu.vue";
import ListTableResponsive from "@/Components/ListTable/ListTableResponsive.vue";
import ListTableResponsiveRow from "@/Components/ListTable/ListTableResponsiveRow.vue";
import ListTableResponsiveCell from "@/Components/ListTable/ListTableResponsiveCell.vue";
import GuiContainer from "@/Components/GUI/GuiContainer.vue";
import LayoutFilters from "@/Components/Layout/LayoutFilters.vue";
import LayoutFiltersItem from "@/Components/Layout/LayoutFiltersItem.vue";
import InputDate from "@/Components/Inputs/InputDate.vue";
import DictionaryDropDown from "@/Components/Inputs/DictionaryDropDown.vue";
import ShowcaseInputDropDown from "@/Pages/Showcase/Components/ShowcaseInputDropDown.vue";
import GuiMessage from "@/Components/GUI/GuiMessage.vue";
import Pagination from "@/Components/Pagination.vue";
import PopUp from "@/Components/PopUp.vue";

export default {
    components: {
        PopUp,
        Pagination,
        GuiMessage,
        ShowcaseInputDropDown,
        DictionaryDropDown,
        InputDate,
        LayoutFiltersItem,
        LayoutFilters,
        GuiContainer,
        ListTableResponsiveCell,
        ListTableResponsiveRow,
        ListTableResponsive,
        GuiActionsMenu,
        LoadingProgress,
        PartnerCodes,
        LayoutRoutedTabs,
        LayoutPage,
    },

    data: () => ({
        list: list('/api/statistics/qrcodes/list'),
        qr_image: null,
    }),

    created() {
        this.list.initial();
    },
    methods: {
        generateQr(qrcode) {
            this.qr_generating = true;
            axios.post('/api/qrcodes/generate', {id: qrcode.qr_code_id})
                .then(response => {
                    this.qr_image = response.data.data['qr'];
                    this.$refs.qr_popup.show();
                })
                .catch(error => {
                    this.$toast.error(error.response.data.message);
                })
                .finally(() => {
                    this.qr_generating = false;
                })
        },
    }


}
</script>
