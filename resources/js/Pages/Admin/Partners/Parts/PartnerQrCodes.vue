<template>
    <LoadingProgress :loading="list.is_loading">
        <LayoutPage :title="$route.meta['title']" :loading="list.is_loading">
            <template #actions>
                <GuiActionsMenu>
                    <div class="link" @click="editItem()">Добавить QR-код</div>
                </GuiActionsMenu>
            </template>
            <ListTableResponsive v-if="list.list.length > 0" :titles="list.titles" :has-action="true">
                <ListTableResponsiveRow v-for="qrcode in list.list">
                    <ListTableResponsiveCell :mobile-title="list.titles[0]">
                        {{qrcode.name}}
                    </ListTableResponsiveCell>
                    <ListTableResponsiveCell :mobile-title="list.titles[1]">
                        {{qrcode.link}}
                    </ListTableResponsiveCell>
                    <ListTableResponsiveCell :mobile-title="list.titles[2]">
                        {{qrcode.statistic_visit_count}}
                    </ListTableResponsiveCell>
                    <ListTableResponsiveCell :mobile-title="list.titles[3]">
                        {{qrcode.statistic_payment_count}}
                    </ListTableResponsiveCell>
                    <ListTableResponsiveCell :mobile-title="list.titles[4]">
                        <span class="link" @click="generateQr(qrcode)">ПОСМОТРЕТЬ</span>
                    </ListTableResponsiveCell>
                    <ListTableResponsiveCell>
                        <GuiActionsMenu :title="null">
                            <span class="link" @click="editItem(qrcode)">Изменить</span>
                        </GuiActionsMenu>
                    </ListTableResponsiveCell>
                </ListTableResponsiveRow>
            </ListTableResponsive>

            <GuiMessage border v-else-if="list.is_loaded">Ничего не найдено</GuiMessage>

            <Pagination :pagination="list.pagination" @pagination="(page, per_page) => list.load(page, per_page)"/>

            <FormPopUp :title="form_title"
                       :form="form"
                       ref="qrcode_form"
            >
                <GuiContainer w-600px>
                    <FormString  :form="form" name="name"/>
                    <FormString  :form="form" name="link"/>
                </GuiContainer>
            </FormPopUp>

            <PopUp :close-on-overlay="true" ref="qr_popup">
                <img :src="qr_image" alt="qr" style="width: 300px"/>
            </PopUp>
        </LayoutPage>
    </LoadingProgress>
</template>

<script>
import LoadingProgress from "@/Components/LoadingProgress";
import GuiHint from "@/Components/GUI/GuiHint";
import GuiHeading from "@/Components/GUI/GuiHeading";
import TicketRate from "@/Pages/Parts/Rates/TicketRate";
import GuiMessage from "@/Components/GUI/GuiMessage";
import GuiActionsMenu from "@/Components/GUI/GuiActionsMenu";
import GuiExpand from "@/Components/GUI/GuiExpand";
import GuiContainer from "@/Components/GUI/GuiContainer";
import PartnerTicketRateOverride from "@/Pages/Admin/Partners/Parts/PartnerTicketRateOverride";
import list from "@/Core/List";
import form from "@/Core/Form";
import LayoutPage from "@/Components/Layout/LayoutPage.vue";
import ListTableResponsive from "@/Components/ListTable/ListTableResponsive.vue";
import ListTableResponsiveCell from "@/Components/ListTable/ListTableResponsiveCell.vue";
import ListTableResponsiveRow from "@/Components/ListTable/ListTableResponsiveRow.vue";
import Pagination from "@/Components/Pagination.vue";
import FormPopUp from "@/Components/FormPopUp.vue";
import FormString from "@/Components/Form/FormString.vue";
import PopUp from "@/Components/PopUp.vue";

export default {
    props: {
        partnerId: {type: Number, required: true},
        editable: {type: Boolean, default: false},
    },

    components: {
        PopUp,
        FormString,
        FormPopUp,
        Pagination,
        ListTableResponsiveRow,
        ListTableResponsiveCell,
        ListTableResponsive,
        LayoutPage,
        PartnerTicketRateOverride,
        GuiContainer,
        GuiExpand,
        GuiActionsMenu,
        GuiMessage,
        TicketRate,
        GuiHeading,
        GuiHint,
        LoadingProgress

    },

    data: () => ({
        list: list('/api/qrcodes/list'),
        form: form(null, '/api/qrcodes/update_or_create'),
        form_title: 'Добавление QR-кода',
        default_link: 'https://city-tours-spb.ru/' +
            '',
        qrcode_link: null,
        qr_image: null,
    }),

    computed: {
        processing() {
            return this.data.is_loading;
        },
    },

    created() {
        this.list.options = {partner_id: this.partnerId};
        this.list.initial();
    },

    methods: {
        editItem(qrcode = null) {
            this.form_title = qrcode ? 'Изменение QR-кода' : 'Добавление QR-кода';
            this.form.reset();
            this.form.set('name', qrcode ? qrcode.name : null, 'required', 'Название', true);
            this.form.set('link', qrcode ? qrcode.link : this.default_link, 'required', 'Ссылка', true);
            this.form.set('partner_id', this.partnerId, 'required', 'Ссылка', true);
            this.form.load();
            this.$refs.qrcode_form.show({id: qrcode ? qrcode.id : null})
                .then(() => {
                    this.list.load();
                });
        },
        generateQr(qrcode) {
            this.qr_generating = true;
            axios.post('/api/qrcodes/generate', {id: qrcode.id})
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
