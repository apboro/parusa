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
        ticket: null,
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
        ticket(newTicket) {
            if (newTicket !== null) {
                this.cameraOn = false;
            }
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
                        this.ticket = response.data.data
                        this.message = null;
                    }
                })
        },
        cameraToggle() {
            this.cameraOn = !this.cameraOn;
        },
        used() {
            this.ticket = null;
            this.cameraOn = true;
        },
        close() {
            this.ticket = null;
            this.cameraOn = true;
        }
    }
}
</script>

<template>
    <GuiContainer w-100 h-80 ph-10 mt-10 center>
        <div style="max-width: 450px; display: inline-block;">
            <GuiButton style="margin-bottom: 30px" v-if="ticket === null" @click="cameraToggle">
                {{ cameraOn ? 'ВЫКЛЮЧИТЬ КАМЕРУ' : 'ВКЛЮЧИТЬ КАМЕРУ' }}
            </GuiButton>
            <qrcode-stream v-if="cameraOn" @paused="cameraOn" @detect="onDetect"></qrcode-stream>
            <CompactTicket v-if="ticket" :ticket="ticket" @used="used" @close="close"/>
            <div style="text-align: center; font-weight: bold; color: red;" v-if="isVisible">{{ message }}</div>
        </div>
    </GuiContainer>
</template>

<style scoped lang="scss">

</style>
