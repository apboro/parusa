<template>
    <template v-if="has_error">
        <CheckoutMessage>
            <div>{{ error_message }}</div>
            <a class="ap-checkout-link" :href="back_link" v-if="back_link">Вернуться к подбору билетов</a>
            <CheckoutButton @clicked="close" v-else style="margin-top: 20px;">Закрыть</CheckoutButton>
        </CheckoutMessage>
    </template>

    <LayoutPage :title="title" :loading="processing" :breadcrumbs="breadcrumbs">
        <GuiContainer w-70>
            <GuiValue :title="'Статус'">{{ info.data['status'] }}<b v-if="isReserve"> до {{ info.data['valid_until'] }}</b></GuiValue>
            <GuiValue :title="'Способ продажи'" v-if="!isReserve">{{ info.data['type'] }}</GuiValue>
            <GuiValue v-if="isReserve" :title="'Кем забронировано'">{{ info.data['partner'] }}<span v-if="info.data['position']">, {{ info.data['position'] }}</span></GuiValue>
            <GuiValue v-else-if="info.data['partner']" :title="info.data['position'] ? 'Продавец' : 'Промоутер'">{{ info.data['partner'] }}<span v-if="info.data['position']">, {{ info.data['position'] }}</span></GuiValue>
            <GuiValue :title="'Касса'" v-if="info.data['terminal']">{{ info.data['terminal'] }}<span v-if="info.data['cashier']">, {{ info.data['cashier'] }}</span></GuiValue>
        </GuiContainer>

        <GuiHeading mt-30 mb-30>{{ isReserve ? 'Состав брони' : 'Состав заказа' }}</GuiHeading>

        <ListTable :titles="['№ билета', 'Отправление', 'Экскурсия, причал', 'Тип билета', 'Стоимость', 'Статус']" :has-action="isReserve || is_returning || is_transfer">
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
                <ListTableCell v-if="is_returning" class="va-middle">
                    <InputCheckbox v-model="to_return" :value="ticket['id']" :disabled="!ticket['returnable']"/>
                </ListTableCell>
                <ListTableCell v-if="is_transfer" class="va-middle">
                    <InputCheckbox v-model="to_transfer" :value="ticket['id']" :disabled="ticket['disabled']" @change="orderTransfer"/>
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
            <GuiHeading mb-20>Информация о плательщике
                <IconEdit class="link w-20px ml-5" style="position: relative; top: 1px;" @click="editInfo"/>
            </GuiHeading>
            <GuiValue :title="'Имя'">{{ info.data['name'] }}</GuiValue>
            <GuiValue :title="'Email'">{{ info.data['email'] }}</GuiValue>
            <GuiValue :title="'Телефон'">{{ info.data['phone'] }}</GuiValue>
        </GuiContainer>

        <GuiContainer v-if="is_transfer" w-50 mt-30 mb-30 inline>
            <GuiHeading mb-20>Дата</GuiHeading>
            <GuiButton :class="'mb-20'" v-for="date in dates" @click="dateClicked(date)">{{ date }}</GuiButton>
            <GuiText v-if="dates.length < 1">Даты не найдены</GuiText>
        </GuiContainer>

        <GuiContainer v-if="is_visible_trips" mb-50>
            <ListTable :titles="['Отправление', '№ Рейса', 'Осталось билетов (всего)', 'Статусы движение / продажа', '']">
                <ListTableRow v-for="trip in trips_by_date">
                    <ListTableCell>
                        <div>
                            <b>
                                <router-link :class="'link'" :to="{name: 'trip-view', params: {id: trip['id']}}">{{ trip['start_time'] }}</router-link>
                            </b>
                        </div>
                        <div>{{ trip['start_date'] }}</div>
                    </ListTableCell>
                    <ListTableCell>
                        <router-link :class="'link'" :to="{name: 'trip-view', params: {id: trip['id']}}">{{ trip['id'] }}</router-link>
                    </ListTableCell>
                    <ListTableCell>
                        {{ trip['tickets_total'] - trip['tickets_count'] }} ({{ trip['tickets_total'] }})
                    </ListTableCell>
                    <ListTableCell>
                        <div>
                            <span :class="{'link': accepted}">{{ trip['status'] }}</span>
                        </div>
                        <div>
                            <span :class="{'link': accepted}" v-if="trip['has_rate']">{{ trip['sale_status'] }}</span>
                            <span class="text-red" v-else><IconExclamation :class="'h-1em inline'"/> Тариф не задан</span>
                        </div>
                    </ListTableCell>
                    <ListTableCell class="va-middle">
                        <GuiButton @clicked="showTripEdit(trip['id'])">Выбрать</GuiButton>
                    </ListTableCell>
                </ListTableRow>
            </ListTable>
        </GuiContainer>

        <template v-if="info.is_loaded">
            <template v-if="!isReserve">
                <GuiContainer mb-20>
                    <GuiButton :disabled="!info.data['is_printable'] || is_returning" @clicked="downloadOrder">Скачать заказ в PDF</GuiButton>
                    <GuiButton :disabled="!info.data['is_printable'] || is_returning || !info.data['email']" @clicked="emailOrder">Отправить клиенту на почту</GuiButton>
                    <GuiButton :disabled="!info.data['is_printable'] || is_returning" @clicked="printOrder">Распечатать</GuiButton>
                    <GuiButton v-if="info.data['can_return']" :disabled="!info.data['returnable'] || returning_progress" @clicked="makeReturn" :color="'red'">Оформить возврат
                    </GuiButton>
                    <GuiButton v-if="info.data['can_return'] && is_returning" :disabled="returning_progress" @clicked="cancelReturn">Отмена</GuiButton>
                </GuiContainer>
                <GuiContainer>
                    <GuiButton v-if="!is_transfer" @clicked="editTransferOrder" :color="'red'">Оформить перенос рейса</GuiButton>
                    <GuiButton v-if="is_transfer" @clicked="editTransferOrder(true)">Отменить</GuiButton>
                </GuiContainer>
            </template>
            <template v-else>
                <GuiContainer text-right>
                    <GuiButton @clicked="discardReserve" :color="'red'">Аннулировать бронь</GuiButton>
                </GuiContainer>
            </template>
        </template>

        <FormPopUp :title="'Информация о плательщике'" :form="form" ref="popup">
            <FormString :form="form" :name="'name'"/>
            <FormString :form="form" :name="'email'"/>
            <FormPhone :form="form" :name="'phone'"/>
        </FormPopUp>

        <PopUp ref="order_transfer_popup" title="Оформление переноса рейса"
               :buttons="[
                    {result: 'yes', caption: 'Да', color: 'red'},
                    {result: 'no', caption: 'Отмена', color: 'white'},
                   ]"
               :manual="true"
        > Вы действительно ли вы хотите изменить дату рейса?
        </PopUp>
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
import {saveAs} from "file-saver";
import IconEdit from "@/Components/Icons/IconEdit";
import form from "@/Core/Form";
import FormPopUp from "@/Components/FormPopUp";
import FormString from "@/Components/Form/FormString";
import FormPhone from "@/Components/Form/FormPhone";
import ListTableResponsive from "@/Components/ListTable/ListTableResponsive.vue";
import ListTableResponsiveRow from "@/Components/ListTable/ListTableResponsiveRow.vue";
import ListTableResponsiveCell from "@/Components/ListTable/ListTableResponsiveCell.vue";
import PopUp from "@/Components/PopUp.vue";
import GuiText from "@/Components/GUI/GuiText.vue";
import IconExclamation from "@/Components/Icons/IconExclamation.vue";
import roles from "@/Mixins/roles.vue";
import CheckoutMessage from "@/Pages/Checkout/Components/CheckoutMessage.vue";
import CheckoutButton from "@/Pages/Checkout/Components/CheckoutButton.vue";

export default {
    components: {
        CheckoutButton,
        CheckoutMessage,
        IconExclamation,
        GuiText,
        PopUp,
        ListTableResponsiveCell,
        ListTableResponsiveRow,
        ListTableResponsive,
        FormPhone,
        FormString,
        FormPopUp,
        IconEdit,
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

    mixins: [DeleteEntry, roles],

    data: () => ({
        info: data('/api/registries/order'),
        form: form(null, '/api/registries/order/buyer'),
        is_returning: false,
        to_return: [],
        is_transfer: false,
        to_transfer: [],
        returning_progress: false,
        dates: [],
        is_visible_trips: false,
        trips: null,
        trips_by_date: null,

        has_error: false,
        error_message: null,
        back_link: null,
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
        },
        accepted() {
            return this.hasRole(['admin', 'office_manager']);
        },
    },

    created() {
        this.info.load({id: this.orderId});
        if (this.$route.query['return']) {
            this.is_returning = true;
        }
        this.form.toaster = this.$toast;
    },

    methods: {
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

        downloadOrder() {
            axios.post('/api/registries/order/download', {id: this.orderId})
                .then(response => {
                    let order = atob(response.data.data['order']);
                    let byteNumbers = new Array(order.length);
                    for (let i = 0; i < order.length; i++) {
                        byteNumbers[i] = order.charCodeAt(i);
                    }
                    let byteArray = new Uint8Array(byteNumbers);
                    let blob = new Blob([byteArray], {type: "application/pdf;charset=utf-8"});

                    saveAs(blob, response.data.data['file_name'], {autoBom: true});
                })
                .catch(error => {
                    this.$toast.error(error.response.data['message']);
                });
        },

        emailOrder() {
            this.$dialog.show('Отправить билет на почту "' + this.info.data['email'] + '"?', 'question', 'orange', [
                this.$dialog.button('yes', 'Продолжить', 'orange'),
                this.$dialog.button('no', 'Отмена', 'blue'),
            ]).then(result => {
                if (result === 'yes') {
                    axios.post('/api/registries/order/send', {id: this.orderId})
                        .then(response => {
                            this.$toast.success(response.data['message']);
                        })
                        .catch(error => {
                            this.$toast.error(error.response.data['message']);
                        });
                }
            });
        },

        printOrder() {
            axios.post('/api/registries/order/download', {id: this.orderId})
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

        editInfo() {
            this.form.reset();
            this.form.set('name', this.info.data['name'], null, 'Имя', true);
            this.form.set('email', this.info.data['email'], 'nullable|email', 'Email', true);
            this.form.set('phone', this.info.data['phone'], null, 'Телефон', true);
            this.form.load();
            this.$refs.popup.show({id: this.orderId})
                .then(result => {
                    this.info.data['name'] = result.values['name'];
                    this.info.data['email'] = result.values['email'];
                    this.info.data['phone'] = result.values['phone'];
                })
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

        editTransferOrder(clear = false) {
            this.is_transfer = !this.is_transfer;

            if (clear) {
                this.to_transfer = [];
                this.dates = [];
                this.is_visible_trips = false;
            }

            this.info.data['tickets'].map((ticket) => {
                ticket['disabled'] = false;
                return ticket;
            });
        },

        orderTransfer() {
            axios.post('/api/order/transfer', {
                id: this.orderId,
                transfers: this.to_transfer,
            })
                .then(response => {
                    this.info.data['tickets'] = response.data.data['tickets'];
                    this.dates = response.data.data['dates'];
                    this.trips = response.data.data['trips'];
                }).finally(() => {
                    this.is_visible_trips = false;
            });
        },

        dateClicked(date) {
            this.trips_by_date = [];
            this.trips.map(item => {
                if (item['start_date'] === date) {
                    this.trips_by_date.push(item);
                }
            });
            this.is_visible_trips = true;
        },

        showTripEdit(tripId) {
            this.$refs.order_transfer_popup.show()
                .then(result => {
                    if (result === 'yes') {
                        axios.post('/api/order/transfer/update', {
                            tripId: tripId,
                            transfers: this.to_transfer,
                        })
                            .then((response) => {
                                this.$toast.success(response.data.message, 5000);
                                this.$refs.order_transfer_popup.hide();
                                this.$router.go();
                            })
                            .catch(error => {
                                this.$toast.error(error.response.data.message, 5000);
                            })
                            .finally(() => {
                                this.$refs.order_transfer_popup.hide();
                            });
                    }
                });
        },
    }
}
</script>
