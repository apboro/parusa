<template>
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
import form from "@/Core/Form";
import FormNumber from "@/Components/Form/FormNumber.vue";
import FormText from "@/Components/Form/FormText.vue";
import FormCheckBox from "@/Components/Form/FormCheckBox.vue";
import FormString from "@/Components/Form/FormString.vue";
import GuiContainer from "@/Components/GUI/GuiContainer.vue";
import FormPopUp from "@/Components/FormPopUp.vue";
import PopUp from "@/Components/PopUp.vue";
import GuiMessage from "@/Components/GUI/GuiMessage.vue";
import Pagination from "@/Components/Pagination.vue";
import GuiButton from "@/Components/GUI/GuiButton.vue";

export default {
    components: {
        Pagination,
        GuiMessage,
        GuiButton,
        PopUp,
        FormPopUp,
        GuiContainer,
        FormString,
        FormCheckBox,
        FormText,
        FormNumber,
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
        list: list('/api/qrcodes/list'),
        form: form(null, '/api/qrcodes/update_or_create'),
        form_title: 'Добавление QR-кода',
        default_link: 'https://city-tours-spb.ru/' +
            '',
        qrcode_link: null,
        qr_image: null,
    }),

    created() {
        this.list.initial();
    },

    methods:{
        editItem(qrcode = null) {
            this.form_title = qrcode ? 'Изменение QR-кода' : 'Добавление QR-кода';
            this.form.reset();
            this.form.set('name', qrcode ? qrcode.name : null, 'required', 'Название', true);
            this.form.set('link', qrcode ? qrcode.link : this.default_link, 'required', 'Ссылка', true);
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
