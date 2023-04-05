<template>
    <div>
        <GuiValueArea mt-30 :title="'Экскурсии'">
            <template v-for="(excursion, key) in data['excursions']">
                <InputCheckbox v-model="excursions" :value="excursion.id" :label="excursion.name" @change="editExcursions"/>
            </template>
        </GuiValueArea>
        <GuiValueArea mt-20 v-if="data['link']" :title="'Ссылка на витрину на сайте компании &quot;Алые Паруса&quot;'">
            <a class="link" :href="data['link'] + (excursions.length ? `&excursions=` + excursions : '')" target="_blank">{{ data['link'] }}{{ excursions.length ? `&excursions=` + excursions : '' }}</a>
        </GuiValueArea>
        <GuiValueArea mt-30 v-if="scripts" :title="'Код для вставки витрины на сайт'" :text-content="scripts"/>
        <GuiValueArea mt-30 :title="'QR-код'">
            <LoadingProgress :loading="qr_generating">
                <GuiText mb-10>Введите URL страницы, на котором размещено приложение "витрина"</GuiText>
                <GuiContainer flex>
                    <InputString v-model="qr_target_page" :original="qr_target_page" :placeholder="'Введите URL страницы'" :class="'mr-15'"/>
                    <GuiButton @clicked="generateQr" :disabled="qr_target_page === null">Получить QR-код</GuiButton>
                </GuiContainer>
            </LoadingProgress>
        </GuiValueArea>
        <GuiValueArea mt-30 :title="'ID промоутера'">{{ data['partner_id'] }}</GuiValueArea>

        <PopUp :close-on-overlay="true" ref="qr_popup">
            <img :src="qr_image" alt="qr" style="width: 300px"/>
        </PopUp>
    </div>
</template>

<script>
import GuiContainer from "@/Components/GUI/GuiContainer";
import GuiHeading from "@/Components/GUI/GuiHeading";
import GuiValueArea from "@/Components/GUI/GuiValueArea";
import InputString from "@/Components/Inputs/InputString";
import GuiButton from "@/Components/GUI/GuiButton";
import GuiText from "@/Components/GUI/GuiText";
import LoadingProgress from "@/Components/LoadingProgress";
import PopUp from "@/Components/PopUp";
import InputCheckbox from "@/Components/Inputs/InputCheckbox.vue";
import data from "@/Core/Data";

export default {
    components: {
        InputCheckbox,
        PopUp,
        LoadingProgress,
        GuiText,
        GuiButton,
        InputString,
        GuiValueArea,
        GuiHeading,
        GuiContainer,
    },

    props: {
        data: {type: Object.required},
    },

    watch: {
        data() {
            this.updateData();
        }
    },

    data: () => ({
        qr_target_page: null,
        qr_generating: false,
        qr_image: null,
        excursions: [],
        scripts: null,
    }),

    created() {
        this.scripts = this.data['code'];
        this.updateData();
    },

    methods: {
        updateData() {
            if (this.data && this.data['qr_target_page']) {
                this.qr_target_page = this.data['qr_target_page'];
            }
        },
        generateQr() {
            this.qr_generating = true;
            axios.post('/api/partners/partner/settings/qr', {url: this.qr_target_page})
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
        editExcursions() {
            axios.post('/api/partners/partner/settings/widget2', {
                excursions: this.excursions
            })
                .then(response => {
                    this.scripts = response.data.data['code'];
                });
        }
    }
}
</script>
