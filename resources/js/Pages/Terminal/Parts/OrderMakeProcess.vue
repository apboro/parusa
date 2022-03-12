<template>
    <GuiHeading mt-20 bold>Заказ №{{ data['order_id'] }}</GuiHeading>
    <LoadingProgress :loading="processing">
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
        <GuiContainer mt-30>
            <GuiButton :color="'green'" :disabled="!data['actions']['start_payment']" @clicked="sendPayment">Отправить в оплату</GuiButton>
            <GuiButton :color="'red'" :disabled="!data['actions']['cancel_order']" @clicked="deleteOrder">Отменить заказ</GuiButton>
        </GuiContainer>
        <GuiContainer mt-30>
            <LoadingProgress :loading="true" v-if="data['status']['waiting_for_payment']">
                <GuiMessage>Ожидание оплаты</GuiMessage>
            </LoadingProgress>
            <GuiButton :color="'red'" :disabled="!data['actions']['cancel_payment']" @clicked="cancelPayment">Отмена оплаты</GuiButton>
        </GuiContainer>
        <GuiContainer mt-30>
            <GuiButton :color="'greed'" :disabled="!data['actions']['print']">Печать билетов</GuiButton>
        </GuiContainer>
        <GuiContainer mt-30>
            <GuiButton :color="'greed'" :disabled="!data['actions']['finish']" @clicked="closeOrder">Закрыть заказ</GuiButton>
        </GuiContainer>
    </LoadingProgress>
</template>

<script>
import GuiHeading from "@/Components/GUI/GuiHeading";
import GuiContainer from "@/Components/GUI/GuiContainer";
import GuiButton from "@/Components/GUI/GuiButton";
import LoadingProgress from "@/Components/LoadingProgress";
import GuiMessage from "@/Components/GUI/GuiMessage";

export default {
    props: {
        data: {type: Object, required: true},
    },

    emits: ['update'],

    components: {
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
    }),

    mounted() {
        this.interval = setInterval(this.handleInterval, 1000);
    },

    unmounted() {
        clearInterval(this.interval);
        this.interval = null;
    },

    methods: {
        handleInterval() {
            if (this.data['status']['waiting_for_payment']) {
                axios.post('/api/order/terminal/status', {})
                    .then(response => {
                        if (response.data.data['waiting_for_pay'] === false) {
                            this.$emit('update');
                            if (!this.print_fired) {
                                this.$toast.info('Print!');
                            }
                        }
                    })
                    .catch(error => {
                        this.$toast.error(error.response.data.data['message']);
                    })
            }
        },
        cancelPayment() {
            this.runAction('/api/order/terminal/cancel');
        },
        sendPayment() {
            this.runAction('/api/order/terminal/send');
        },
        closeOrder() {
            this.runAction('/api/order/terminal/close');
        },
        deleteOrder() {
            this.$dialog.show('Расформировать заказ?', 'question', 'red', [
                this.$dialog.button('yes', 'Расформировать', 'red'),
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
                .then(response => {
                    this.$toast.success(response.data.message, 5000);
                    this.$emit('update');
                })
                .catch(error => {
                    this.$toast.error(error.response.data.message, 5000);
                })
                .finally(() => {
                    this.processing = false;
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
