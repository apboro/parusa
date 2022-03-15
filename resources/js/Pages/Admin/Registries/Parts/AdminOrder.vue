<template>
    <LayoutPage :title="title" :loading="processing" :breadcrumbs="breadcrumbs">
        <GuiContainer w-70>
            <GuiValue :title="'Статус'">{{ info.data['status'] }}<b v-if="isReserve"> до {{ info.data['valid_until'] }}</b></GuiValue>
            <GuiValue :title="'Способ продажи'" v-if="!isReserve">{{ info.data['type'] }}</GuiValue>
            <GuiValue :title="'Касса'" v-if="info.data['terminal']">{{ info.data['terminal'] }}</GuiValue>
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
                        <GuiIconButton :title="'Удалить из брони'" :border="false" :color="'red'" @click="removeTicketFromReserve(ticket)">
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
                <ListTableCell v-if="isReserve"/>
            </ListTableRow>
        </ListTable>

        <GuiContainer w-50 mt-30 mb-30 inline>
            <GuiHeading mb-20>Информация о плательщике</GuiHeading>
            <GuiValue :title="'Имя'">{{ info.data['name'] }}</GuiValue>
            <GuiValue :title="'Email'">{{ info.data['email'] }}</GuiValue>
            <GuiValue :title="'Телефон'">{{ info.data['phone'] }}</GuiValue>
        </GuiContainer>

        <template v-if="info.is_loaded">
            <template v-if="!isReserve">
                <GuiContainer>
                    <GuiButton :disabled="!info.data['is_actual']" @clicked="in_dev">Скачать заказ в PDF</GuiButton>
                    <GuiButton :disabled="!info.data['is_actual']" @clicked="in_dev">Отправить клиенту на почту</GuiButton>
                    <GuiButton :disabled="!info.data['is_actual']" @clicked="in_dev">Распечатать</GuiButton>
                </GuiContainer>
            </template>
            <template v-else>
                <GuiContainer text-right>
                    <GuiButton @clicked="in_dev" :color="'red'">Аннулировать бронь</GuiButton>
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
import InputCheckbox from "@/Components/Inputs/InputCheckbox";
import InputText from "@/Components/Inputs/InputText";
import DeleteEntry from "@/Mixins/DeleteEntry";

export default {
    components: {
        InputText,
        InputCheckbox,
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

    mixins: [DeleteEntry],

    data: () => ({
        info: data('/api/registries/order'),
    }),

    computed: {
        title() {
            return (this.info.data['is_reserve'] ? 'Бронь' : 'Заказ') + ' №' + this.orderId;
        },
        isReserve() {
            return Boolean(this.info.data['is_reserve']);
        },
        processing() {
            return this.info.is_loading || this.deleting;
        },
        breadcrumbs() {
            if (this.isReserve) {
                return [{caption: 'Реестр броней', to: {name: 'reserves-registry'}}];
            }
            return [{caption: 'Реестр заказов', to: {name: 'orders-registry'}}];
        }
    },

    created() {
        this.info.load({id: this.orderId});
        if (this.$route.query['return']) {
            this.is_returning = true;
        }
    },

    methods: {
        in_dev() {
            this.$toast.info('В разработке');
        },

        discardReserve() {
            if (!this.isReserve) {
                return;
            }

            this.deleteEntry(`Аннулировать бронь №${this.orderId}?`, '/api/order/reserve/cancel', {id: this.orderId})
                .then(() => {

                })
        },

        removeTicketFromReserve(ticket) {
            if (!this.isReserve) {
                return;
            }

            this.deleteEntry(`Удалить билет №${ticket['id']} из брони?`, '/api/order/reserve/remove', {id: this.orderId, ticket_id: ticket['id']})
                .then(response => {
                    if (response.data.payload['reserve_cancelled']) {

                    } else {
                        this.info.load({id: this.orderId});
                    }
                })
        },
    }
}
</script>
