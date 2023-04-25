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
            <GuiValue v-else-if="info.data['partner']" :title="info.data['position'] ? 'Продавец' : 'Промоутер'">{{ info.data['partner'] }}<span
                v-if="info.data['position']">, {{ info.data['position'] }}</span></GuiValue>
            <GuiValue :title="'Касса'" v-if="info.data['terminal']">{{ info.data['terminal'] }}<span v-if="info.data['cashier']">, {{ info.data['cashier'] }}</span></GuiValue>
        </GuiContainer>

        <GuiHeading mt-30 mb-30>{{ isReserve ? 'Состав брони' : 'Состав заказа' }}</GuiHeading>

        <ListTable :titles="['№ билета', 'Отправление', 'Экскурсия, причал', 'Тип билета', 'Стоимость', 'Статус']" :has-action="isReserve || is_returning || is_replacement">
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
            <GuiHeading mb-20>Информация о плательщике
                <IconEdit v-if="!is_returning && !is_replacement" class="link w-20px ml-5" style="position: relative; top: 1px;" @click="editInfo"/>
            </GuiHeading>
            <GuiValue :title="'Имя'">{{ info.data['name'] }}</GuiValue>
            <GuiValue :title="'Email'">{{ info.data['email'] }}</GuiValue>
            <GuiValue :title="'Телефон'">{{ info.data['phone'] }}</GuiValue>
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

        <template v-if="info.is_loaded">
            <template v-if="!isReserve">
                <GuiContainer mb-20>
                    <GuiButton :disabled="!info.data['is_printable'] || is_returning || is_replacement" @clicked="downloadOrder">Скачать заказ в PDF</GuiButton>
                    <GuiButton :disabled="!info.data['is_printable'] || is_returning || is_replacement || !info.data['email']" @clicked="emailOrder">Отправить клиенту на почту</GuiButton>
                    <GuiButton :disabled="!info.data['is_printable'] || is_returning || is_replacement" @clicked="printOrder">Распечатать</GuiButton>
                </GuiContainer>
                <GuiContainer>
                    <GuiButton v-if="info.data['can_return']" :disabled="!info.data['returnable'] || returning_progress || is_replacement" @clicked="makeReturn" :color="'red'">Оформить возврат
                    </GuiButton>
                    <GuiButton v-if="info.data['can_return'] && is_returning" :disabled="returning_progress" @clicked="cancelReturn">Отмена</GuiButton>
                    <GuiButton v-if="!is_replacement" @clicked="replaceTickets" :color="'red'">Оформить перенос рейса</GuiButton>
                    <GuiButton v-if="is_replacement" @clicked="replaceTickets(true)">Отменить</GuiButton>
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
import GuiMessage from "@/Components/GUI/GuiMessage";
import InputDate from "@/Components/Inputs/InputDate";

export default {
    components: {
        InputDate,
        GuiMessage,
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
        is_replacement: false,
        to_replace: [],

        excursion_id: null,
        returning_progress: false,
        dates: null,
        replacement_date: null,
        replacement_trips: null,

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
            return this.info.is_loading || this.deleting || this.returning_progress;
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
