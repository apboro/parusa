<template>
    <LoadingProgress :loading="processing && !externalProcessing">
        <GuiHeading mt-20 bold>Заказ №{{ data['order_id'] }}</GuiHeading>

        <GuiText v-if="data['status']['created'] && data['is_reserve']" mt-30 mb-30>
            Сформирован заказ №{{ data['order_id'] }} из брони №{{ data['order_id'] }}
        </GuiText>

        <template v-if="data['status']['created']">
            <table class="order-table">
                <thead>
                <tr>
                    <th>№ билета</th>
                    <th>Дата</th>
                    <th>Время</th>
                    <th>Экскурсия</th>
                    <th>Причал</th>
                    <th>Тип билета</th>
                    <th>Цена, руб</th>
                </tr>
                </thead>
                <tr v-for="ticket in data['order_tickets']">
                    <td>{{ ticket['id'] }}</td>
                    <td>{{ ticket['trip_start_date'] }}</td>
                    <td>{{ ticket['trip_start_time'] }}</td>
                    <td>
                        <div>{{ ticket['excursion'] }}</div>
                    </td>
                    <td>{{ ticket['pier'] }}</td>
                    <td>{{ ticket['grade'] }}</td>
                    <td>{{ ticket['base_price'] }}</td>
                </tr>
            </table>
            <GuiHeading mt-20 bold right>Итого: {{ data['order_total'] }} руб.</GuiHeading>
        </template>

        <GuiContainer mt-30 text-right v-if="data['status']['created']">
            <GuiButton :color="'green'" :disabled="!data['actions']['start_payment']" @clicked="sendPayment">Оплатить через терминал</GuiButton>
            <GuiButton :color="'red'" :disabled="!data['actions']['cancel_order']" @clicked="deleteOrder">Аннулировать заказ</GuiButton>
        </GuiContainer>

        <GuiMessage v-if="data['status']['waiting_for_payment']">Заказ отправлен на терминал. Идёт оплата...</GuiMessage>
        <GuiContainer mt-30 text-right v-if="data['status']['waiting_for_payment']">
            <GuiButton :color="'red'" :disabled="!data['actions']['cancel_payment']" @clicked="cancelPayment">Отмена оплаты</GuiButton>
        </GuiContainer>

        <GuiMessage v-if="data['status']['finishing']">Заказ оплачен.</GuiMessage>
        <GuiContainer mt-30 text-right v-if="data['status']['finishing']">
            <GuiButton :color="'green'" :disabled="!data['actions']['print']" @clicked="printOrder">Печать билетов</GuiButton>
            <GuiButton :disabled="!data['actions']['finish']" @clicked="closeOrder">Закрыть заказ</GuiButton>
        </GuiContainer>
    </LoadingProgress>
</template>

<script>
import GuiHeading from "@/Components/GUI/GuiHeading";
import GuiContainer from "@/Components/GUI/GuiContainer";
import GuiButton from "@/Components/GUI/GuiButton";
import LoadingProgress from "@/Components/LoadingProgress";
import GuiMessage from "@/Components/GUI/GuiMessage";
import GuiText from "@/Components/GUI/GuiText";
import printJS from "print-js";

export default {
    props: {
        data: {type: Object, required: true},
        externalProcessing: {type: Boolean, default: false},
    },

    emits: ['update'],

    components: {
        GuiText,
        GuiMessage,
        LoadingProgress,
        GuiButton,
        GuiContainer,
        GuiHeading,
    },

    data: () => ({
        processing: false,
        interval: null,
        print_fired: false,
        order_cancelling: false,
    }),

    mounted() {
        this.interval = setInterval(this.handleInterval, 1000);
    },

    unmounted() {
        clearInterval(this.interval);
        this.interval = null;
    },

    computed() {

    },

    methods: {
        handleInterval() {
            if (this.data['status']['waiting_for_payment'] && !this.order_cancelling) {
                axios.post('/api/order/terminal/status', {})
                    .then(response => {
                        if (response.data.data['waiting_for_pay'] === false) {
                            this.$emit('update');
                            if (!this.print_fired) {
                                this.printOrder();
                            }
                        }
                    })
                    .catch(error => {
                        this.$toast.error(error.response.data.data['message']);
                    })
            }
        },

        sendPayment() {
            this.order_cancelling = false;
            this.runAction('/api/order/terminal/send');
        },

        cancelPayment() {
            this.order_cancelling = true;
            this.runAction('/api/order/terminal/cancel');
        },

        closeOrder() {
            this.runAction('/api/order/terminal/close');
        },

        deleteOrder() {
            this.$dialog.show('Аннулировать заказ?', 'question', 'red', [
                this.$dialog.button('yes', 'Продолжить', 'red'),
                this.$dialog.button('no', 'Отмена', 'blue'),
            ]).then(result => {
                if (result === 'yes') {
                    this.runAction('/api/order/terminal/delete');
                }
            });
        },

        runAction(url) {
            if (this.processing) {
                return;
            }
            this.processing = true;
            axios.post(url, {})
                .then(() => {
                    this.$emit('update');
                    this.$store.dispatch('terminal/refresh');
                })
                .catch(error => {
                    this.$toast.error(error.response.data.message, 5000);
                })
                .finally(() => {
                    this.processing = false;
                });
        },

        printOrder() {
            axios.post('/api/registries/order/print', {id: this.data['order_id'] })
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
    }
}
</script>

<style lang="scss" scoped>
$project_font: -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica Neue, Arial, Noto Sans, sans-serif, Apple Color Emoji, Segoe UI Emoji, Segoe UI Symbol, Noto Color Emoji !default;
$base_black_color: #1e1e1e !default;

.order-table {
    font-size: 14px;
    font-family: $project_font;
    border-collapse: collapse;
    margin: 10px 0 0;
    width: 100%;
    color: $base_black_color;

    & thead {
        color: #424242;
    }

    & th {
        text-align: left;
        padding: 10px;
        cursor: default;
        vertical-align: middle;
    }

    & td {
        vertical-align: middle;
        padding: 5px 10px;
        cursor: default;
    }
}
</style>
