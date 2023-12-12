<script>
import seatMethods from "@/Mixins/seatMethods.vue";

export default {
    name: "SelectedTickets",
    props: {tickets: Object},
    mixins: [seatMethods]
}

</script>

<template>
    <div v-if="tickets.length > 0" class="ap-tickets-container">
        <span>Выбранные билеты:</span>
        <div v-for="ticket in tickets" class="ap-tickets-box">
            <span><b>Место:</b> {{ ticket.seatNumber }}</span>
            <span><b>Билет:</b> {{ ticket.grade.name }}</span>
            <span v-if="ticket.menu !== null"><b>Меню:</b> {{ ticket.menu.name }}</span>
            <span><b>Цена:</b> {{ ticket.price }} руб.</span>
        </div>
        <span style="margin-top: 15px;"><b>Итого:</b> {{ schemeTotal() }} руб.</span>
    </div>
</template>

<style scoped lang="scss">

</style>
