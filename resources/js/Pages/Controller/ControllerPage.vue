<script>
import {QrcodeStream, QrcodeDropZone, QrcodeCapture} from 'vue-qrcode-reader'
import GuiButton from "@/Components/GUI/GuiButton.vue";
import GuiContainer from "@/Components/GUI/GuiContainer.vue";
import TicketInfoPage from "@/Pages/Admin/Registries/TicketInfoPage.vue";
import CompactTicket from "@/Components/CompactTicket.vue";
import GuiText from "@/Components/GUI/GuiText.vue";
import GuiValue from "@/Components/GUI/GuiValue.vue";
import InputNumber from "@/Components/Inputs/InputNumber.vue";

export default {
    name: "ControllerPage",

    data: () => ({
        data: null,
        message: null,
        paused: false,
        ticketNumber: null
    }),

    components: {
        InputNumber,
        GuiValue,
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
            this.paused = newValue !== null;
        }
    },

    methods: {
        async onDetect(detectedCode) {
            await this.timeout(1500)

            await axios.post('/api/ticket/qrcode/check', detectedCode)
                .then(response => {
                    if (response.data.data.notValidQrCode) {
                        this.message = response.data.data.notValidQrCode;
                    } else {
                        this.data = response.data.data
                        this.message = null;
                    }
                })
            if (this.message !== null) {
                await this.timeout(2000)
                this.message = null;
                location.reload()
            }
        },
        timeout(ms) {
            return new Promise((resolve) => {
                window.setTimeout(resolve, ms)
            })
        },
        manualEnter() {
            axios.post('/api/ticket/qrcode/check', {manual: true, ticketNumber: this.ticketNumber})
                .then(response => {
                    if (response.data.data.notValidQrCode) {
                        this.message = response.data.data.notValidQrCode;
                    } else {
                        this.data = response.data.data
                        this.message = null;
                    }
                })
        },

        paintBoundingBox(detectedCodes, ctx) {
            for (const detectedCode of detectedCodes) {
                const {
                    boundingBox: {x, y, width, height}
                } = detectedCode
                ctx.lineWidth = 2
                ctx.strokeStyle = '#124dee'
                ctx.strokeRect(x, y, width, height)
            }
        },

        cameraToggle() {
            this.paused = !this.paused;
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
                {{ paused ? 'ВКЛЮЧИТЬ КАМЕРУ' : 'ВЫКЛЮЧИТЬ КАМЕРУ' }}
            </GuiButton>
            <qrcode-stream v-if="!paused" @paused="paused" @detect="onDetect" :track="paintBoundingBox">
                <div v-if="message !== null" class="validation-failure">{{ message }}</div>
            </qrcode-stream>

            <div v-if="!data">
                <span>Ввести номер билета вручную</span>
                <InputNumber :small="true" placeholder="Введите номер билета" v-model="ticketNumber"></InputNumber>
                <GuiButton style="margin-top: 10px" @clicked="manualEnter">Найти</GuiButton>
            </div>

            <CompactTicket v-if="data" :data="data" @used="used" @close="close"/>

        </div>
    </GuiContainer>
</template>

<style scoped lang="scss">

.validation-failure {
    position: absolute;
    width: 100%;
    height: 100%;

    background-color: rgba(255, 255, 255, 0.8);
    padding: 10px;
    text-align: center;
    font-weight: bold;
    font-size: 1.4rem;
    color: black;

    display: flex;
    flex-flow: column nowrap;
    justify-content: center;
}

.validation-failure {
    color: red;
}
</style>
