<script>
import GuiContainer from "@/Components/GUI/GuiContainer.vue";
import GuiText from "@/Components/GUI/GuiText.vue";
import GuiValue from "@/Components/GUI/GuiValue.vue";
import GuiHeading from "@/Components/GUI/GuiHeading.vue";
import GuiButton from "@/Components/GUI/GuiButton.vue";

export default {
    name: "CompactTicket",
    components: {GuiButton, GuiHeading, GuiValue, GuiText, GuiContainer},

    props: {
        ticket: null,
    },

    methods: {
        useTicket(){
            axios.post('http://localhost:8000/api/ticket/qrcode/use', {ticketId: this.ticket.ticket.ticket_id})
        },
        close(){
            location.reload();
        }
    }

}
</script>

<template>
        <GuiContainer ml-50 w-30>
            <GuiText bold v-if="ticket.notValidTicket">{{ticket.notValidTicket}}</GuiText>
            <GuiValue :title="'Номер билета:'">{{ticket.ticket.ticket_id}}</GuiValue>
            <GuiValue :title="'Номер заказа:'">{{ticket.ticket.order_id}}</GuiValue>
            <GuiValue :title="'Номер рейса:'">{{ticket.ticket.trip_id}}</GuiValue>
            <GuiValue :title="'Статус билета:'">{{ticket.ticket.ticket_status}}</GuiValue>
            <GuiValue :title="'Начало рейса:'">{{ticket.ticket.trip_start_time}}</GuiValue>
            <GuiValue :title="'Экскурсия:'">{{ticket.ticket.excursion_name}}</GuiValue>
            <GuiValue :title="'Причал отправления:'">{{ticket.ticket.pier}}</GuiValue>
            <GuiButton v-if="!ticket.notValidTicket" @click="useTicket()" :color="'green'">ОТМЕТИТЬ КАК ИСПОЛЬЗОВАННЫЙ</GuiButton>
            <GuiButton :color="'red'" @click="close()">ЗАКРЫТЬ</GuiButton>
    </GuiContainer>
</template>

<style scoped lang="scss">

</style>
