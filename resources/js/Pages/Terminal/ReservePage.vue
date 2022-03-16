<template>
    <LayoutPage :title="title" :loading="processing" :breadcrumbs="[{caption: 'Реестр броней', to: {name: 'reserve-search'}}]">
        <GuiContainer w-70>
            <GuiValue :title="'Статус'">{{ info.data['status'] }}<b> до {{ info.data['valid_until'] }}</b></GuiValue>
            <GuiValue :title="'Кем забронировано'">{{ info.data['position'] ? info.data['position'] + ', ' : '' }} {{ info.data['partner'] }}</GuiValue>
        </GuiContainer>

        <GuiHeading mt-30 mb-30>Состав брони</GuiHeading>

        <ListTable :titles="['№ билета', 'Отправление', 'Экскурсия, причал', 'Тип билета', 'Стоимость', 'Статус']" :has-action="true">
            <ListTableRow v-for="ticket in info.data['tickets']">
                <ListTableCell>
                    {{ ticket['id'] }}
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
                <ListTableCell class="va-middle">
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
                <ListTableCell colspan="2"/>
            </ListTableRow>
        </ListTable>

        <GuiContainer w-50 mt-30 mb-30 inline>
            <GuiHeading mb-20>Информация о плательщике</GuiHeading>
            <GuiValue :title="'Имя'">{{ info.data['name'] }}</GuiValue>
            <GuiValue :title="'Email'">{{ info.data['email'] }}</GuiValue>
            <GuiValue :title="'Телефон'">{{ info.data['phone'] }}</GuiValue>
        </GuiContainer>

        <template v-if="info.is_loaded">
            <GuiContainer text-right>
                <GuiButton @clicked="order" :color="'green'" v-if="info.data['can_buy']">Сформировать заказ</GuiButton>
                <!--
                <GuiButton @clicked="discardReserve" :color="'red'">Аннулировать бронь</GuiButton>
                -->
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

    mixins: [DeleteEntry],

    data: () => ({
        info: data('/api/registries/order'),
        ordering: false,
    }),

    computed: {
        orderId() {
            return Number(this.$route.params.id);
        },
        title() {
            return 'Бронь №' + this.orderId;
        },
        processing() {
            return this.info.is_loading || this.deleting || this.ordering;
        },
    },

    created() {
        this.info.load({id: this.orderId, reserve: true});
    },

    methods: {
        in_dev() {
            this.$toast.info('В разработке');
        },

        /**
         discardReserve() {
            this.deleteEntry(`Аннулировать бронь №${this.orderId}?`, '/api/order/reserve/cancel', {id: this.orderId, reserve: true})
                .then(() => {
                    this.$router.push({name: 'reserve-search'});
                })
        },
         **/

        removeTicketFromReserve(ticket) {
            this.deleteEntry(`Удалить билет №${ticket['id']} из брони?`, '/api/order/reserve/remove', {id: this.orderId, ticket_id: ticket['id'], reserve: true})
                .then(response => {
                    if (response.data.payload['reserve_cancelled']) {
                        this.$router.push({name: 'reserve-search'});
                    } else {
                        this.info.load({id: this.orderId, reserve: true});
                    }
                })
        },

        order() {
            this.$dialog.show('Сформировать заказ для передачи в оплату?', 'question', 'orange', [
                this.$dialog.button('ok', 'Продолжить', 'orange'),
                this.$dialog.button('cancel', 'Отмена'),
            ], 'center')
                .then((result) => {
                    if (result === 'ok') {
                        this.ordering = true;
                        axios.post('/api/order/reserve/accept', {id: this.orderId})
                            .then((response) => {
                                this.$toast.success(response.data['message']);
                                this.$router.push({name: 'order'});
                            })
                            .catch(error => {
                                this.$toast.error(error.response.data['message']);
                            })
                            .finally(() => {
                                this.ordering = false;
                            })
                    }
                });
        },
    }
}
</script>
