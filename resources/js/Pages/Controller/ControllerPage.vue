<script>
import { QrcodeStream, QrcodeDropZone, QrcodeCapture } from 'vue-qrcode-reader'
export default {
    name: "ControllerPage",

    data: () => ({
       cameraOn: false,
    }),

    components: {
        QrcodeStream,
        QrcodeDropZone,
        QrcodeCapture
    },

    methods: {
        onDetect(detectedCode){
            console.log(detectedCode)
            axios.post('http://localhost:8000/api/ticket/qrcode/check', detectedCode).then(response => console.log(response.data))
        },
        cameraToggle(){
            this.cameraOn = !this.cameraOn;
        }
    }
}
</script>

<template>
    <button @click="onDetect">KNOPA</button>
    <button @click="cameraToggle">CAMERA TOGGLE</button>
    <qrcode-drop-zone @detect="onDetect"><div style ="margin-left: 30%; width: 450px; height: 200px; border-style: solid;">DROP ZONE</div></qrcode-drop-zone>
    <div style ="margin-left: 30%; width: 450px; height: 500px"><qrcode-stream @camera-on="cameraOn" @detect="onDetect"></qrcode-stream></div>
</template>

<style scoped lang="scss">

</style>
