<template>
    <LayoutPage :title="title" :loading="info.is_loading">
        <GuiContainer w-70>
            <GuiValue :title="isReserve ? 'Номер брони' : 'Номер заказа'">
                <router-link class="link" :to="{name: 'order-info', params: {id: info.data['order_id'] }}" v-if="info.is_loaded">{{ info.data['order_id'] }}</router-link>
            </GuiValue>
            <GuiValue :title="isReserve ? 'Дата и время бронирования' : 'Дата и время продажи'">{{ info.data['sold_at'] }}</GuiValue>
            <GuiValue :title="'Способ продажи'" v-if="!isReserve">{{ info.data['order_type'] }}</GuiValue>
            <GuiValue :title="isReserve ? 'Кем забронировано' : 'Продавец'">{{ info.data['position'] ? info.data['position'] + ', ' : '' }} {{ info.data['partner'] }}</GuiValue>
            <GuiValue :title="'Статус'">{{ info.data['status'] }}</GuiValue>
        </GuiContainer>

        <GuiHeading mt-30 mb-30>Информация о билете</GuiHeading>
        <GuiContainer w-70>
            <GuiValue :title="'Экскурсия'">{{ info.data['excursion'] }}</GuiValue>
            <GuiValue :title="'Причал'">{{ info.data['pier'] }}</GuiValue>
            <GuiValue :title="'Дата и время отправления'">{{ info.data['trip_start_date'] }}, {{ info.data['trip_start_time'] }} (рейс №{{ info.data['trip_id'] }})</GuiValue>
            <GuiValue :title="'Тип билета'">{{ info.data['grade'] }}</GuiValue>
            <GuiValue :title="'Цена'">{{ info.data['base_price'] }} руб.</GuiValue>
        </GuiContainer>

        <GuiHeading mt-30 mb-30>Информация о плательщике</GuiHeading>

        <GuiContainer w-70 mb-50>
            <GuiValue :title="'Имя'">{{ info.data['name'] }}</GuiValue>
            <GuiValue :title="'Email'">{{ info.data['email'] }}</GuiValue>
            <GuiValue :title="'Телефон'">{{ info.data['phone'] }}</GuiValue>
        </GuiContainer>

        <template v-if="info.is_loaded && !isReserve">
            <GuiContainer>
                <GuiButton @click="in_dew">Скачать билет в PDF</GuiButton>
                <GuiButton @click="in_dew">Отправить клиенту на почту</GuiButton>
                <GuiButton @click="in_dew">Отправить клиенту СМС</GuiButton>
                <GuiButton @click="in_dew">Распечатать</GuiButton>
                <GuiButton @click="in_dew" :color="'red'">Оформить возврат</GuiButton>
            </GuiContainer>
        </template>
    </LayoutPage>
</template>

<script>
import data from "@/Core/Data";
import LayoutPage from "@/Components/Layout/LayoutPage";
import GuiContainer from "@/Components/GUI/GuiContainer";
import GuiValue from "@/Components/GUI/GuiValue";
import GuiHeading from "@/Components/GUI/GuiHeading";
import GuiButton from "@/Components/GUI/GuiButton";

export default {
    components: {
        GuiButton,
        GuiHeading,
        GuiValue,
        GuiContainer,
        LayoutPage,
    },

    props: {
        ticketId: {type: Number, required: true},
    },

    data: () => ({
        info: data('/api/registries/ticket'),
    }),

    computed: {
        title() {
            return 'Билет' + ' №' + this.ticketId;
        },
        isReserve() {
            return this.info.data['is_order_reserve'];
        }
    },

    created() {
        this.info.load({id: this.ticketId})
    },

    methods: {
        in_dew() {
            this.$toast.info('Функционал находится в разработке');
        },
    }
}
</script>
