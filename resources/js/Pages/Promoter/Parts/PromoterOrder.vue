<template>
    <LayoutPage :title="title" :loading="processing" :breadcrumbs="breadcrumbs">
        <GuiContainer w-70>
            <GuiValue :title="'Статус'">{{ info.data['status'] }}<b v-if="isReserve"> до {{
                    info.data['valid_until']
                }}</b></GuiValue>
            <GuiValue :title="'Способ продажи'" v-if="!isReserve">{{ info.data['type'] }}</GuiValue>
            <GuiValue v-if="isReserve" :title="'Кем забронировано'">{{ info.data['partner'] }}<span
                v-if="info.data['position']">, {{ info.data['position'] }}</span></GuiValue>
            <GuiValue v-else-if="info.data['partner']" :title="info.data['position'] ? 'Продавец' : 'Промоутер'">
                {{ info.data['partner'] }}<span v-if="info.data['position']">, {{ info.data['position'] }}</span>
            </GuiValue>
            <GuiValue :title="'Касса'" v-if="info.data['terminal']">{{ info.data['terminal'] }}<span
                v-if="info.data['cashier']">, {{ info.data['cashier'] }}</span></GuiValue>
        </GuiContainer>

        <GuiHeading mt-30 mb-30>{{ isReserve ? 'Состав брони' : 'Состав заказа' }}</GuiHeading>

        <ListTable :titles="['№ билета', 'Отправление', 'Экскурсия, причал', 'Тип билета', 'Стоимость', 'Статус']"
                   :has-action="isReserve || is_returning">
            <ListTableRow v-for="ticket in info.data['tickets']">
                <ListTableCell>
                    <router-link class="link" :to="{name: 'ticket-info', params: {id: ticket['id']}}">{{
                            ticket['id']
                        }}
                    </router-link>
                    <div style="color: red;" v-if="ticket.isBackward">обратный</div>
                </ListTableCell>
                <ListTableCell>
                    <div v-if="!ticket.is_single_ticket">{{ ticket['trip_start_date'] }}</div>
                    <div v-if="!ticket.is_single_ticket">{{ ticket['trip_start_time'] }}</div>
                    <div v-if="ticket.is_single_ticket">ЕДИНЫЙ</div>
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
                        <GuiIconButton :title="'Удалить из брони'" :border="false" :color="'red'"
                                       @click="removeTicketFromReserve(ticket)">
                            <IconCross/>
                        </GuiIconButton>
                    </div>
                </ListTableCell>
                <ListTableCell v-if="is_returning" class="va-middle">
                    <InputCheckbox v-model="to_return" :value="ticket['id']" :disabled="!ticket['returnable']"/>
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
            <GuiValue :title="'Телефон'" required>{{ info.data['phone'] }}</GuiValue>
        </GuiContainer>
        <GuiContainer w-50 mt-30 mb-30 inline pl-20 v-if="is_returning">
            <GuiHeading mb-20>Причина возврата</GuiHeading>
            <InputText v-model="reason_text"/>
        </GuiContainer>

        <template v-if="info.is_loaded">
            <GuiContainer>
                <GuiButton :disabled="!info.data['is_printable'] || !info.data['email'] || is_returning"
                           @clicked="emailOrder">Отправить на почту
                </GuiButton>
                <GuiButton @click="showQR">QR-код на оплату</GuiButton>
            </GuiContainer>
        </template>

        <FormPopUp :title="'Информация о плательщике'" :form="form" ref="popup">
            <FormString :form="form" :name="'name'"/>
            <FormString :form="form" :name="'email'"/>
            <FormPhone :form="form" :name="'phone'"/>
        </FormPopUp>

        <PopUp :close-on-overlay="true" :title="'QR-код для оплаты заказа'" ref="qrpopup">
            <img :src="qr_image" alt="qr" style="width: 300px"/>
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
import form from "@/Core/Form";
import FormPopUp from "@/Components/FormPopUp";
import FormString from "@/Components/Form/FormString";
import FormPhone from "@/Components/Form/FormPhone";
import IconEdit from "@/Components/Icons/IconEdit";
import {saveAs} from "file-saver";
import PopUp from "@/Components/PopUp.vue";

export default {
    components: {
        PopUp,
        IconEdit,
        FormPhone,
        FormString,
        FormPopUp,
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
        is_returning: false,
        to_return: [],
        reason_text: null,
        returning_progress: false,
        ordering: false,
        qr_image: null,
        form: form(null, '/api/registries/order/buyer'),
    }),

    computed: {
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
        this.form.toaster = this.$toast;
    },

    methods: {
        showQR(){
                axios.post('/api/order/generate_payment_code', {orderId: this.orderId})
                    .then(response => {
                        this.qr_image = response.data.data['qr'];
                        this.$refs.qrpopup.show();
                    })
                    .catch(error => {
                        this.$toast.error(error.response.data.message);
                    })
            },

        sendSMS() {
            axios.post('/api/order/send_sms', {orderId: this.orderId})
                .then((response) => {
                    this.$toast.success(response.data.message, 2100);
                })
                .catch(error => {
                    this.$toast.error(error.response.data.message, 2100);
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

            this.deleteEntry(`Удалить билет №${ticket['id']} из брони?`, '/api/order/reserve/remove', {
                id: this.orderId,
                ticket_id: ticket['id']
            })
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
            this.form.set('phone', this.info.data['phone'], 'required', 'Телефон', true);
            this.form.load();
            this.$refs.popup.show({id: this.orderId})
                .then(result => {
                    this.info.data['name'] = result.values['name'];
                    this.info.data['email'] = result.values['email'];
                    this.info.data['phone'] = result.values['phone'];
                })
        },
    }
}
</script>
