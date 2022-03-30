<template>
    <div class="ap-checkout">
        <LoadingProgress :loading="processing" :opacity="100">
            <template v-if="!has_error">
                <h2 class="ap-checkout__header">Заказ №{{ order['id'] }}</h2>

                <table class="ap-checkout__table">
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
                    <tbody>
                    <tr v-for="ticket in order['tickets']">
                        <td data-label="№ билета: ">{{ ticket['id'] }}</td>
                        <td data-label="Дата: ">{{ ticket['trip_start_date'] }}</td>
                        <td data-label="Время: ">{{ ticket['trip_start_time'] }}</td>
                        <td data-label="Экскурсия: ">{{ ticket['excursion'] }}</td>
                        <td data-label="Причал: ">{{ ticket['pier'] }}</td>
                        <td data-label="Тип билета: ">{{ ticket['grade'] }}</td>
                        <td data-label="Цена: ">{{ ticket['base_price'] }} руб.</td>
                    </tr>
                    </tbody>
                </table>
                <div class="ap-checkout__total">Итого: {{ order['total'] }} руб.</div>
                <h2 class="ap-checkout__header">Контактные данные</h2>
                <div class="ap-checkout__contact">Имя: {{ order['name'] }}</div>
                <div class="ap-checkout__contact">Email: {{ order['email'] }}</div>
                <div class="ap-checkout__contact">Телефон: {{ order['phone'] }}</div>
                <div class="ap-checkout__countdown">Время на оплату: {{ remind }}</div>
                <div class="ap-checkout__actions">
                    <form method="POST" accept-charset="UTF-8" enctype="application/x-www-form-urlencoded" :action="payment['url']" id="ap-checkout-form" ref="form">
                        <input type="hidden" name="cost" :value="payment['cost']"/>
                        <input type="hidden" name="name" :value="payment['name']"/>
                        <input type="hidden" name="email" :value="payment['email']"/>
                        <input type="hidden" name="service_id" :value="payment['service_id']"/>
                        <input type="hidden" name="order_id" :value="payment['order_id']"/>
                        <input type="hidden" name="version" :value="payment['version']"/>
                        <input type="hidden" name="comment" :value="payment['comment']"/>
                        <input type="hidden" name="payment_type" :value="payment['payment_type']"/>
                        <input type="hidden" name="check" :value="payment['check']"/>
                    </form>
                    <!--                    <GuiButton @click="cancel">Отмена</GuiButton>-->
                    <GuiButton @click="checkout" :color="'green'">Оплатить</GuiButton>
                </div>
            </template>

            <template v-if="has_error">
                <GuiMessage>{{ error_message }}</GuiMessage>
                <a class="ap-checkout__link" :href="back_link" v-if="back_link">Вернуться к оформлению</a>
            </template>
        </LoadingProgress>
    </div>
</template>

<script>
import LoadingProgress from "@/Components/LoadingProgress";
import GuiMessage from "@/Components/GUI/GuiMessage";
import GuiButton from "@/Components/GUI/GuiButton";

export default {
    name: "CheckoutApp",

    props: {
        crm_url: {type: String, default: 'https://cp.parus-a.ru'},
        debug: {type: Boolean, default: false},
    },

    components: {
        GuiButton,
        GuiMessage,
        LoadingProgress,
    },

    data: () => ({
        processing: true,

        has_error: false,
        error_message: null,
        back_link: null,
        order: {},
        order_encrypted: null,
        payment: {},
        lifetime: null,
        updater: null,
    }),

    computed: {
        remind() {
            if (this.lifetime === null) {
                return null;
            }
            const minutes = Math.trunc(this.lifetime / 60);
            const seconds = Math.trunc(this.lifetime - minutes * 60);

            return minutes + ':' + (String(seconds).length === 1 ? '0' + seconds : seconds);
        }
    },

    created() {
        this.init();
        this.updater = setInterval(this.updateLifetime, 1000);
    },

    beforeUnmount() {
        clearInterval(this.updater);
    },

    methods: {
        url(path) {
            return this.crm_url + path + (this.debug ? '?XDEBUG_SESSION_START=PHPSTORM' : '');
        },
        init() {
            const urlParams = new URLSearchParams(window.location.search);
            let parameters = {};
            if (urlParams.has('order')) {
                parameters['order'] = urlParams.get('order');
                this.order_encrypted = parameters['order'];
            } else if (urlParams.has('tid')) {
                parameters['response'] = {};
                for (let param of urlParams) {
                    parameters['response'][param[0]] = param[1];
                }
            }
            this.processing = true;

            axios.post(this.url('/checkout/handle'), parameters)
                .then(response => {
                    this.order = response.data['order'];
                    this.payment = response.data['payment'];
                    this.lifetime = response.data['lifetime'];
                    this.processing = false;
                })
                .catch(error => {
                    this.error_message = error.response.data['message'];
                    this.has_error = true;
                    if (error.response.status === 301) {
                        window.location.href = error.response.data['to'];
                    } else {
                        this.processing = false;
                    }
                    this.back_link = typeof error.response.data.payload['backlink'] !== "undefined" ? error.response.data.payload['backlink'] : null;
                });
        },
        updateLifetime() {
            if (this.lifetime === null) {
                return;
            }
            if (this.lifetime === 0) {
                this.lifetimeExpired();
            }
            this.lifetime -= 1;
        },
        lifetimeExpired() {
            this.init();
        },
        // cancel() {
        // axios.post(this.url('/checkout/cancel'), {order: order})
        //     .then(response => {
        //         this.order = response.data['order'];
        //         this.payment = response.data['payment'];
        //         this.lifetime = response.data['lifetime'];
        //     })
        //     .catch(error => {
        //         this.error_message = error.response.data['message'];
        //         this.back_link = error.response.data.payload['backlink'];
        //         this.has_error = true;
        //     })
        //     .finally(() => {
        //         this.is_initializing = false;
        //     });
        // },
        checkout() {
            this.processing = true;
            axios.post(this.url('/checkout/pay'), {order: this.order_encrypted})
                .then(() => {
                    this.$refs.form.submit();
                })
                .catch(error => {
                    this.error_message = error.response.data['message'];
                    this.back_link = error.response.data.payload['backlink'];
                    this.has_error = true;
                    this.processing = false;
                })
        },
    }
}
</script>

<style lang="scss" scoped>
$project_font: -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica Neue, Arial, Noto Sans, sans-serif, Apple Color Emoji, Segoe UI Emoji, Segoe UI Symbol, Noto Color Emoji !default;

.ap-checkout {
    &__header {
        font-family: $project_font;
        margin: 10px 0 15px 0;
        font-weight: bold;
        font-size: 20px;
    }

    &__total {
        font-family: $project_font;
        font-size: 20px;
        text-align: right;
        font-weight: bold;
    }

    &__contact {
        font-family: $project_font;
        font-size: 16px;
        margin-bottom: 8px;
    }

    &__countdown {
        font-family: $project_font;
        font-size: 18px;
        font-weight: bold;
        text-align: right;
        margin-top: 10px;
        color: #a50000;
    }

    &__actions {
        text-align: right;
        margin-top: 15px;
    }

    &__link {
        font-family: $project_font;
        font-size: 18px;
        color: #024fa1;
    }
}

.ap-checkout__table {
    font-family: $project_font;
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;

    & thead {
        & tr {
            border-bottom: 1px solid #afafaf;
        }

        & th {
            text-align: left;
            font-size: 14px;
            padding: 10px;
            white-space: nowrap;
        }
    }

    & tbody {
        & tr {
            border-bottom: 1px solid #afafaf;
        }

        & td {
            text-align: left;
            font-size: 14px;
            padding: 10px;
            vertical-align: middle;
        }
    }
}

@media screen and (max-width: 850px) {
    .ap-checkout {
        max-width: 500px;
        margin: 0 auto;
    }
    .ap-checkout__table {
        max-width: 500px;
        margin: 0 auto;

        thead {
            border: none;
            clip: rect(0 0 0 0);
            height: 1px;
            margin: -1px;
            overflow: hidden;
            padding: 0;
            position: absolute;
            width: 1px;
        }

        tr {
            border-bottom: none !important;
        }

        tr:not(:first-child) td:first-child {
            padding-top: 40px !important;
        }

        td {
            display: block;
            text-align: right;
            padding: 5px 0 !important;

            &:not(:last-child) {
                border-bottom: 1px solid #e5e5e5;
            }
        }

        td:before {
            content: attr(data-label);
            float: left;
            margin-right: 10px;
            font-weight: bold;
        }
    }
}
</style>
