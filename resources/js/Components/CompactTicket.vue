<script>
import GuiContainer from "@/Components/GUI/GuiContainer.vue";
import GuiText from "@/Components/GUI/GuiText.vue";
import GuiValue from "@/Components/GUI/GuiValue.vue";

export default {
    name: "CompactTicket",
    components: {GuiValue, GuiText, GuiContainer},

    props: {
        ticket: null,
    }

}
</script>

<template>

        <GuiContainer w-70>
            <GuiValue :title="isReserve ? 'Номер брони' : 'Номер заказа'">
                <router-link class="link" :to="{name: 'order-info', params: {id: info.data['order_id'] }}" v-if="info.is_loaded">{{ info.data['order_id'] }}</router-link>
            </GuiValue>
            <GuiValue :title="isReserve ? 'Дата и время бронирования' : 'Дата и время продажи'">{{ info.data['sold_at'] }}</GuiValue>
            <GuiValue :title="'Способ продажи'" v-if="!isReserve">{{ info.data['order_type'] }}</GuiValue>
            <GuiValue :title="isReserve ? 'Кем забронировано' : 'Продавец'">{{ info.data['position'] ? info.data['position'] + ', ' : '' }} {{ info.data['partner'] }}</GuiValue>
            <GuiValue :title="'Статус'">{{ info.data['status'] }}</GuiValue>

    </GuiContainer>
</template>

<style scoped lang="scss">

</style>
