<script>
import GuiContainer from "@/Components/GUI/GuiContainer.vue";
import GuiText from "@/Components/GUI/GuiText.vue";
import GuiValue from "@/Components/GUI/GuiValue.vue";
import GuiHeading from "@/Components/GUI/GuiHeading.vue";
import GuiButton from "@/Components/GUI/GuiButton.vue";

export default {
    name: "CompactTicket",
    components: {GuiButton, GuiHeading, GuiValue, GuiText, GuiContainer},

    data: () => ({
        hidden: true,
    }),

    props: {
        ticket: null,
    },

    emits: ['used', 'close'],

    methods: {
        useTicket() {
            axios.post('/api/ticket/qrcode/use', {ticketId: this.ticket.ticket.ticket_id})
                .then(response => {
                    this.$toast.success(response.data['message'], 2000);
                })
            this.$emit('used');
        },
        close() {
            this.$emit('close')
        }
    }

}
</script>

<template>
    <GuiText bold v-if="ticket.notValidTicket">{{ ticket.notValidTicket }}</GuiText>
    <GuiContainer v-if="ticket.ticket">
        <GuiValue :title="'Номер билета:'">{{ ticket.ticket.ticket_id }}</GuiValue>
        <GuiValue :title="'Тип билета:'">{{ ticket.ticket.type }}</GuiValue>
        <GuiValue :title="'Номер заказа:'">{{ ticket.ticket.order_id }}</GuiValue>
        <GuiValue :title="'Номер рейса:'">{{ ticket.ticket.trip_id }}</GuiValue>
        <GuiValue :title="'Статус билета:'">{{ ticket.ticket.ticket_status }}</GuiValue>
        <GuiValue :title="'Начало рейса:'">{{ ticket.ticket.trip_start_time }}</GuiValue>
        <GuiValue :title="'Экскурсия:'">{{ ticket.ticket.excursion_name }}</GuiValue>
        <GuiValue :title="'Причал:'">{{ ticket.ticket.pier }}</GuiValue>
        <p @click="hidden = !hidden">подробнее ...</p>
        <div v-if="!hidden">
            <GuiValue v-if="ticket.ticket.customer_fio" :title="'Клиент:'">{{ ticket.ticket.customer_fio }}</GuiValue>
            <GuiValue :title="'Телефон клиента:'">{{ ticket.ticket.customer_phone }}</GuiValue>
            <GuiValue v-if="ticket.ticket.promocode" :title="'Промокод:'">{{ ticket.ticket.promocode }}</GuiValue>
        </div>
        <GuiButton style="margin-top: 30px" v-if="!ticket.notValidTicket" @click="useTicket()" :color="'green'">ОТМЕТИТЬ
            КАК ИСПОЛЬЗОВАННЫЙ
        </GuiButton>
    </GuiContainer>
    <GuiButton style="margin-top: 40px" :color="'red'" @click="close()">ЗАКРЫТЬ</GuiButton>

</template>

<style scoped lang="scss">

</style>
