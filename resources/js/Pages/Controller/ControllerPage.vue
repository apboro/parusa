<script>
import { QrcodeStream, QrcodeDropZone, QrcodeCapture } from 'vue-qrcode-reader'
export default {
    name: "ControllerPage",

    components: {
        QrcodeStream,
        QrcodeDropZone,
        QrcodeCapture
    },

    methods: {
        onDetect(detectedCode){
            console.log(detectedCode)
        }
    }
}
</script>

<template>
    <qrcode-stream @detect="onDetect"></qrcode-stream>
</template>

<style scoped lang="scss">

</style>
