<script>
import {QrcodeStream, QrcodeDropZone, QrcodeCapture} from 'vue-qrcode-reader'
import GuiButton from "@/Components/GUI/GuiButton.vue";
import GuiContainer from "@/Components/GUI/GuiContainer.vue";
import TicketInfoPage from "@/Pages/Admin/Registries/TicketInfoPage.vue";
import CompactTicket from "@/Components/CompactTicket.vue";
import GuiText from "@/Components/GUI/GuiText.vue";

export default {
    name: "ControllerPage",

    data: () => ({
        cameraOn: false,
        data: null,
        message: null,
        isVisible: false,
    }),

    components: {
        GuiText,
        CompactTicket,
        TicketInfoPage,
        GuiContainer,
        GuiButton,
        QrcodeStream,
        QrcodeDropZone,
        QrcodeCapture
    },

    watch: {
        data(newValue) {
            this.cameraOn = newValue === null;
        },
        isVisible(newVal) {
            if (newVal) {
                setTimeout(() => {
                    this.isVisible = false;
                }, 2100);
            }
        },
    },

    methods: {
        onDetect(detectedCode) {
            axios.post('/api/ticket/qrcode/check', detectedCode)
                .then(response => {
                    if (response.data.data.notValidQrCode) {
                        this.message = response.data.data.notValidQrCode;
                        this.isVisible = true;
                    } else {
                        this.data = response.data.data
                        this.message = null;
                    }
                })
        },
        cameraToggle() {
            this.cameraOn = !this.cameraOn;
        },
        used() {
            this.data = null;
        },
        close() {
            this.data = null;
        }
    }
}
</script>

<template>
    <GuiContainer w-100 h-80 ph-10 mt-10 center>
        <div style="max-width: 450px; display: inline-block;">
            <GuiButton style="margin-bottom: 30px" v-if="!data" @click="cameraToggle">
                {{ cameraOn ? 'ВЫКЛЮЧИТЬ КАМЕРУ' : 'ВКЛЮЧИТЬ КАМЕРУ' }}
            </GuiButton>
            <qrcode-drop-zone @detect="onDetect">
                <div style="margin-left: 30%; width: 450px; height: 200px; border-style: solid;">DROP ZONE</div>
            </qrcode-drop-zone>
            <qrcode-stream v-if="cameraOn" @paused="cameraOn" @detect="onDetect"></qrcode-stream>
            <CompactTicket v-if="data" :data="data" @used="used" @close="close"/>
            <div style="text-align: center; font-weight: bold; color: red;" v-if="isVisible">{{ message }}</div>
        </div>
    </GuiContainer>
</template>

<style scoped lang="scss">

</style>
