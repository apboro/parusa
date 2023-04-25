<template>
    <LayoutPage :title="title" :loading="processing" :breadcrumbs="breadcrumbs">
        <GuiContainer w-70>
            <GuiValue :title="'Статус'">{{ info.data['status'] }}<b v-if="isReserve"> до {{ info.data['valid_until'] }}</b></GuiValue>
            <GuiValue :title="'Способ продажи'" v-if="!isReserve">{{ info.data['type'] }}</GuiValue>
            <GuiValue v-if="isReserve" :title="'Кем забронировано'">{{ info.data['partner'] }}<span v-if="info.data['position']">, {{ info.data['position'] }}</span></GuiValue>
            <GuiValue v-else-if="info.data['partner']" :title="info.data['position'] ? 'Продавец' : 'Промоутер'">{{ info.data['partner'] }}<span v-if="info.data['position']">, {{ info.data['position'] }}</span></GuiValue>
            <GuiValue :title="'Касса'" v-if="info.data['terminal']">{{ info.data['terminal'] }}<span v-if="info.data['cashier']">, {{ info.data['cashier'] }}</span></GuiValue>
        </GuiContainer>

        <GuiHeading mt-30 mb-30>{{ isReserve ? 'Состав брони' : 'Состав заказа' }}</GuiHeading>

        <ListTable :titles="['№ билета', 'Отправление', 'Экскурсия, причал', 'Тип билета', 'Стоимость', 'Статус']" :has-action="isReserve || is_returning || is_replacement">
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
                <ListTableCell v-if="isReserve" class="va-middle">
                    <div>
                        <GuiIconButton :title="'Удалить из брони'" :border="false" :color="'red'" @click="removeTicketFromReserve(ticket)">
                            <IconCross/>
                        </GuiIconButton>
                    </div>
                </ListTableCell>
                <ListTableCell v-if="is_returning" class="va-middle">
                    <InputCheckbox v-model="to_return" :value="ticket['id']" :disabled="!ticket['returnable']"/>
                </ListTableCell>
                <ListTableCell v-if="is_replacement" class="va-middle">
                    <InputCheckbox v-model="to_replace" :value="ticket['id']"
                                   :disabled="!ticket['transferable'] || (this.excursion_id !== null && this.excursion_id !== ticket['excursion_id'])"
                                   @change="replacementTicketSelected(ticket['excursion_id'])"/>
                </ListTableCell>
            </ListTableRow>
            <ListTableRow :no-highlight="true">
                <ListTableCell colspan="3"/>
                <ListTableCell><b>Итого: {{ info.data['tickets_count'] }}</b></ListTableCell>
                <ListTableCell><b>{{ info.data['total'] }} руб.</b></ListTableCell>
                <ListTableCell/>
                <ListTableCell v-if="isReserve || is_returning"/>
            </ListTableRow>
        </ListTable>

        <GuiContainer w-50 mt-30 mb-30 inline>
            <GuiHeading mb-20>Информация о плательщике</GuiHeading>
            <GuiValue :title="'Имя'">{{ info.data['name'] }}</GuiValue>
            <GuiValue :title="'Email'">{{ info.data['email'] }}</GuiValue>
            <GuiValue :title="'Телефон'">{{ info.data['phone'] }}</GuiValue>
        </GuiContainer>
        <GuiContainer w-50 mt-30 mb-30 inline pl-20 v-if="is_returning">
            <GuiHeading mb-20>Причина возврата</GuiHeading>
            <InputText v-model="reason_text"/>
        </GuiContainer>

        <GuiContainer v-if="is_replacement" w-50 mt-30 mb-30 inline pl-30>
            <GuiMessage v-if="dates === null" border>Выберите билеты для замены</GuiMessage>
            <GuiMessage v-else-if="dates.length === 0" border>Нет подходящих рейсов</GuiMessage>
            <template v-else>
                <GuiHeading mb-20>Выберите дату рейса</GuiHeading>
                <InputDate v-model="replacement_date" :dates="dates" placeholder="Выберите дату" @change="replacementDateSelected"/>
                <GuiMessage v-if="replacement_trips && replacement_trips.length === null" border>На выбранную дату нет рейсов с достаточным количеством свободных мест</GuiMessage>
            </template>
        </GuiContainer>

        <GuiContainer v-if="replacement_trips && replacement_trips.length" mb-50>
            <ListTable :titles="['Отправление', '№ Рейса', 'Экскурсия', 'Осталось билетов', 'Статусы движение / продажа', '']">
                <ListTableRow v-for="trip in replacement_trips">
                    <ListTableCell>
                        <div>
                            <b>{{ trip['start_time'] }}</b>
                        </div>
                        <div>{{ trip['start_date'] }}</div>
                    </ListTableCell>
                    <ListTableCell>
                        {{ trip['id'] }}
                    </ListTableCell>
                    <ListTableCell>
                        {{ trip['excursion'] }}
                    </ListTableCell>
                    <ListTableCell>
                        {{ trip['tickets_total'] - trip['tickets_count'] }}
                    </ListTableCell>
                    <ListTableCell>
                        <div>{{ trip['status'] }}</div>
                        <div>{{ trip['sale_status'] }}</div>
                    </ListTableCell>
                    <ListTableCell class="va-middle">
                        <GuiButton @clicked="selectReplacementTrip(trip['id'])">Выбрать</GuiButton>
                    </ListTableCell>
                </ListTableRow>
            </ListTable>
        </GuiContainer>

        <div v-if="info.is_loaded && !isReserve" class="flex">
            <GuiContainer inline-flex v-if="printable">
                <GuiButton :disabled="!info.data['is_printable'] || is_returning" @clicked="printOrder">Распечатать</GuiButton>
            </GuiContainer>
            <GuiContainer inline-flex grow justify-end v-if="returnable">
                <GuiButton v-if="info.data['can_return']" :disabled="!info.data['returnable'] || returning_progress" @clicked="makeReturn" :color="'red'">Оформить возврат
                </GuiButton>
                <GuiButton v-if="info.data['can_return'] && is_returning" :disabled="returning_progress" @clicked="cancelReturn">Отмена</GuiButton>
            </GuiContainer>

            <GuiContainer ml-20>
                <GuiButton v-if="!is_replacement" @clicked="replaceTickets" :color="'red'">Оформить перенос рейса</GuiButton>
                <GuiButton v-if="is_replacement" @clicked="replaceTickets(true)">Отменить</GuiButton>
            </GuiContainer>
        </div>
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
import printJS from "print-js";
import GuiMessage from "@/Components/GUI/GuiMessage.vue";
import InputDate from "@/Components/Inputs/InputDate.vue";

export default {
    props: {
        returnable: {type: Boolean, required: true},
        printable: {type: Boolean, required: true},
    },

    components: {
        InputDate,
        GuiMessage,
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
        is_returning: false,
        to_return: [],
        reason_text: null,
        returning_progress: false,
        ordering: false,
        is_replacement: false,
        to_replace: [],

        excursion_id: null,
        dates: null,
        replacement_date: null,
        replacement_trips: null,

        has_error: false,
        error_message: null,
        back_link: null,
    }),

    computed: {
        orderId() {
            return Number(this.$route.params.id);
        },
        title() {
            return (this.info.data['is_reserve'] ? 'Бронь' : 'Заказ') + ' №' + this.orderId;
        },
        isReserve() {
            return Boolean(this.info.data['is_reserve']);
        },
        processing() {
            return this.info.is_loading || this.deleting || this.ordering;
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

        makeReturn() {
            if (this.is_returning === false) {
                this.to_return = [];
                this.is_returning = true;
                return;
            }

            if (this.to_return.length === 0) {
                this.$toast.error('Не выбраны билеты для возврата', 3000);
                return;
            }
            this.$dialog.show('Подтвердите оформление возврата', 'question', 'red', [
                this.$dialog.button('yes', 'Продолжить', 'red'),
                this.$dialog.button('no', 'Отмена', 'blue'),
            ]).then(result => {
                if (result === 'yes') {
                    // logic
                    this.returning_progress = true;
                    axios.post('/api/order/return', {
                        id: this.orderId,
                        tickets: this.to_return,
                        reason: this.reason_text,
                    })
                        .then((response) => {
                            this.$toast.success(response.data.message, 5000);
                            this.info.load({id: this.orderId});
                        })
                        .catch(error => {
                            this.$toast.error(error.response.data.message, 5000);
                        })
                        .finally(() => {
                            this.returning_progress = false;
                            this.is_returning = false;
                        });
                }
            });
        },

        cancelReturn() {
            this.is_returning = false;
        },

        discardReserve() {
            if (!this.isReserve) {
                return;
            }

            this.deleteEntry(`Аннулировать бронь №${this.orderId}?`, '/api/order/reserve/cancel', {id: this.orderId})
                .then(() => {
                    this.$router.push({name: 'reserves-registry'});
                })
        },

        removeTicketFromReserve(ticket) {
            if (!this.isReserve) {
                return;
            }

            this.deleteEntry(`Удалить билет №${ticket['id']} из брони?`, '/api/order/reserve/remove', {id: this.orderId, ticket_id: ticket['id']})
                .then(response => {
                    if (response.data.payload['reserve_cancelled']) {
                        this.$router.push({name: 'reserves-registry'});
                    } else {
                        this.info.load({id: this.orderId});
                    }
                })
        },

        order() {
            this.$dialog.show('Выкупить бронь и оплатить с лицевого счёта?', 'question', 'orange', [
                this.$dialog.button('ok', 'Продолжить', 'orange'),
                this.$dialog.button('cancel', 'Отмена'),
            ], 'center')
                .then((result) => {
                    if (result === 'ok') {
                        this.ordering = true;
                        axios.post('/api/order/reserve/order', {id: this.orderId})
                            .then((response) => {
                                this.$toast.success(response.data['message']);
                                this.info.load({id: this.orderId});
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

        printOrder() {
            axios.post('/api/registries/order/print', {id: this.orderId })
                .then(response => {
                    let order = atob(response.data.data['order']);
                    let byteNumbers = new Array(order.length);
                    for (let i = 0; i < order.length; i++) {
                        byteNumbers[i] = order.charCodeAt(i);
                    }
                    let byteArray = new Uint8Array(byteNumbers);
                    let blob = new Blob([byteArray], {type: "application/pdf;charset=utf-8"});
                    let pdfUrl = URL.createObjectURL(blob);
                    printJS(pdfUrl);
                })
                .catch(error => {
                    this.$toast.error(error.response.data['message']);
                });
        },

        replaceTickets(clear = false) {
            this.is_replacement = !this.is_replacement;
            if (clear) {
                this.to_replace = [];
                this.dates = [];
                this.replacement_trips = null;
                this.replacement_date = null;
            }
        },

        replacementTicketSelected(excursion_id) {
            if (this.to_replace.length === 0) {
                this.excursion_id = null;
                this.dates = null;
                this.replacement_trips = null;
                this.replacement_date = null
            } else {
                if (this.excursion_id === null) {
                    this.returning_progress = true;
                    axios.post('/api/order/replacement/get_available_dates', {excursion_id: excursion_id})
                        .then(response => {
                            this.dates = response.data.data['dates'];
                        }).finally(() => {
                        this.returning_progress = false;
                    });
                }
                if (this.replacement_date !== null) {
                    this.replacementDateSelected(this.replacement_date);
                }
                this.excursion_id = excursion_id;
            }
        },

        replacementDateSelected(date) {
            this.returning_progress = true;
            axios.post('/api/order/replacement/get_trips_for_date', {
                date: date,
                excursion_id: this.excursion_id,
                count: this.to_replace ? this.to_replace.length : null
            })
                .then((response) => {
                    this.replacement_trips = response.data.data['trips'];
                })
                .catch(error => {
                    this.replacement_trips = null;
                    this.$toast.error(error.response.data.message, 5000);
                })
                .finally(() => {
                    this.returning_progress = false;
                });
        },

        selectReplacementTrip(tripId) {
            this.$dialog.show('Перенести билеты на другой рейс рейса?', 'question', 'orange', [
                this.$dialog.button('yes', 'Продолжить', 'orange'),
                this.$dialog.button('no', 'Отмена', 'blue'),
            ]).then(result => {
                if (result === 'yes') {
                    this.returning_progress = true;
                    axios.post('/api/order/replacement/make', {
                        order_id: this.orderId,
                        trip_id: tripId,
                        tickets: this.to_replace,
                    })
                        .then((response) => {
                            this.$toast.success(response.data.message, 5000);
                            this.replaceTickets(true);
                            this.info.load({id: this.orderId});
                        })
                        .catch(error => {
                            this.$toast.error(error.response.data.message, 5000);
                        })
                        .finally(() => {
                            this.returning_progress = false;
                        });
                }
            });
        },
    }
}
</script>
