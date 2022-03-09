<template>
    <LayoutPage :title="title" :loading="info.is_loading">
        <GuiContainer w-70>
            <GuiValue :title="'Статус'">{{ info.data['status'] }}<b v-if="isReserve"> до {{ info.data['valid_until'] }}</b></GuiValue>
            <GuiValue :title="'Способ продажи'" v-if="!isReserve">{{ info.data['type'] }}</GuiValue>
            <GuiValue :title="isReserve ? 'Кем забронировано' : 'Продавец'">{{ info.data['position'] ? info.data['position'] + ', ' : '' }} {{ info.data['partner'] }}</GuiValue>
        </GuiContainer>

        <GuiHeading mt-30 mb-30>{{ isReserve ? 'Состав брони' : 'Состав заказа' }}</GuiHeading>

        <ListTable :titles="['№ билета', 'Отправление', 'Экскурсия, причал', 'Тип билета', 'Стоимость', 'Статус']" :has-action="isReserve">
            <ListTableRow v-for="ticket in info.data['tickets']">
                <ListTableCell>
                    <router-link class="link" :to="{name: 'ticket-info', params: {id: ticket['id']}}">{{ ticket['id'] }}</router-link>
                </ListTableCell>
                <ListTableCell>
                    <div>{{ ticket['trip_start_date'] }}</div>
                    <div>{{ ticket['trip_start_time'] }}</div>
                </ListTableCell>
                <ListTableCell>
                    <div>{{ ticket['excursion'] }}</div>
                    <div>{{ ticket['pier'] }}</div>
                </ListTableCell>
                <ListTableCell>{{ ticket['grade'] }}</ListTableCell>
                <ListTableCell>{{ ticket['base_price'] }} руб.</ListTableCell>
                <ListTableCell>{{ ticket['status'] }}</ListTableCell>
                <ListTableCell v-if="isReserve" class="va-middle">
                    <div>
                        <GuiIconButton :title="'Удалить из брони'" :border="false" :color="'red'" @click="in_dew">
                            <IconCross/>
                        </GuiIconButton>
                    </div>
                </ListTableCell>
            </ListTableRow>
            <ListTableRow :no-highlight="true">
                <ListTableCell colspan="3"/>
                <ListTableCell><b>Итого: {{ info.data['tickets_count'] }}</b></ListTableCell>
                <ListTableCell><b>{{ info.data['total'] }} руб.</b></ListTableCell>
                <ListTableCell/>
            </ListTableRow>
        </ListTable>

        <GuiHeading mt-30 mb-30>Информация о плательщике</GuiHeading>

        <GuiContainer w-70 mb-50>
            <GuiValue :title="'Имя'">{{ info.data['name'] }}</GuiValue>
            <GuiValue :title="'Email'">{{ info.data['email'] }}</GuiValue>
            <GuiValue :title="'Телефон'">{{ info.data['phone'] }}</GuiValue>
        </GuiContainer>

        <template v-if="info.is_loaded">
            <template v-if="!isReserve">
                <GuiContainer>
                    <GuiButton @click="in_dew">Скачать заказ в PDF</GuiButton>
                    <GuiButton @click="in_dew">Отправить клиенту на почту</GuiButton>
                    <GuiButton @click="in_dew">Распечатать</GuiButton>
                    <GuiButton @click="in_dew" :color="'red'">Оформить возврат</GuiButton>
                </GuiContainer>
            </template>
            <template v-else>
                <GuiContainer text-right>
                    <GuiButton @click="in_dew" :color="'green'" v-if="info.data['can_buy']">Выкупить бронь</GuiButton>
                    <GuiButton @click="in_dew" :color="'red'">Аннулировать бронь</GuiButton>
                </GuiContainer>
            </template>
        </template>
    </LayoutPage>
</template>

<script>
import data from "@/Core/Data";
import LayoutPage from "@/Components/Layout/LayoutPage";
import GuiContainer from "@/Components/GUI/GuiContainer";
import GuiValue from "@/Components/GUI/GuiValue";
import GuiHeading from "@/Components/GUI/GuiHeading";
import ListTable from "@/Components/ListTable/ListTable";
import ListTableRow from "@/Components/ListTable/ListTableRow";
import ListTableCell from "@/Components/ListTable/ListTableCell";
import GuiButton from "@/Components/GUI/GuiButton";
import GuiIconButton from "@/Components/GUI/GuiIconButton";
import IconCross from "@/Components/Icons/IconCross";

export default {
    components: {
        IconCross,
        GuiIconButton,
        GuiButton,
        ListTableCell,
        ListTableRow,
        ListTable,
        GuiHeading,
        GuiValue,
        GuiContainer,
        LayoutPage,
    },

    props: {
        orderId: {type: Number, required: true},
    },

    data: () => ({
        info: data('/api/registries/order'),
    }),

    computed: {
        title() {
            return (this.info.data['is_reserve'] ? 'Бронь' : 'Заказ') + ' №' + this.orderId;
        },
        isReserve() {
            return Boolean(this.info.data['is_reserve']);
        }
    },

    created() {
        this.info.load({id: this.orderId})
    },

    methods: {
        in_dew() {
            this.$toast.info('Функционал находится в разработке');
        },
    }
}
</script>
