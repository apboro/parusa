<script>
import {QrcodeStream, QrcodeDropZone, QrcodeCapture} from 'vue-qrcode-reader'
import GuiButton from "@/Components/GUI/GuiButton.vue";
import GuiContainer from "@/Components/GUI/GuiContainer.vue";
import TicketInfoPage from "@/Pages/Admin/Registries/TicketInfoPage.vue";
import CompactTicket from "@/Components/CompactTicket.vue";

export default {
    name: "ControllerPage",

    data: () => ({
        cameraOn: false,
        ticket: null,
    }),

    components: {
        CompactTicket,
        TicketInfoPage,
        GuiContainer,
        GuiButton,
        QrcodeStream,
        QrcodeDropZone,
        QrcodeCapture
    },

    methods: {
        onDetect(detectedCode) {
            axios.post('http://localhost:8000/api/ticket/qrcode/check', detectedCode)
                .then(response => this.ticket = response.data.data)
        },
        cameraToggle() {
            this.cameraOn = !this.cameraOn;
        }
    }
}
</script>

<template>
    <!--    <button @click="onDetect">KNOPA</button>-->
    <GuiContainer mt-10 center>
        <GuiButton @click="cameraToggle">{{cameraOn ? 'ВЫКЛЮЧИТЬ КАМЕРУ' : 'ВКЛЮЧИТЬ КАМЕРУ'}}</GuiButton>
        <qrcode-drop-zone @detect="onDetect">
            <div style="margin-left: 30%; width: 450px; height: 200px; border-style: solid;">DROP ZONE</div>
        </qrcode-drop-zone>
        <div v-if="cameraOn" style="margin-left: 30%; width: 450px; height: 500px">
            <qrcode-stream @paused="cameraOn" @detect="onDetect"></qrcode-stream>
        </div>
        <CompactTicket :ticket="ticket"/>
    </GuiContainer>
</template>

<style scoped lang="scss">

</style>
