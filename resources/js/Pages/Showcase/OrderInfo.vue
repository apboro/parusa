<template>
    <div class="ap-final-message">
        <GuiMessage>Заказ № {{ order_id }} оплачен.</GuiMessage>
        <GuiText>Билеты отправлены Вам на почту, указанную при оформлении.</GuiText>
        <br/>
        <GuiText>Инструкции, если клиент не получил билеты.</GuiText>
        <br/>
        <GuiButton @click="close">Закрыть</GuiButton>
    </div>
</template>

<script>
import GuiMessage from "@/Components/GUI/GuiMessage";
import GuiButton from "@/Components/GUI/GuiButton";
import {mapGetters} from "vuex";
import GuiText from "@/Components/GUI/GuiText";

export default {

    emits: ['close'],

    components: {GuiText, GuiButton, GuiMessage},

    computed: {
        ...mapGetters('showcase', ['order_id'])
    },

    methods: {
        close() {
            this.$store.commit('showcase/order', null);
            this.$store.commit('showcase/order_id', null);
            this.$emit('close');
        }
    }
}
</script>

<style scoped>
.ap-final-message, .ap-final-message > * {
    text-align: center;
}
</style>
