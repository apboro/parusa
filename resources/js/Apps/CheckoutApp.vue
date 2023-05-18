<template>
    <div class="ap-checkout">
        <CheckoutLoadingProgress :loading="processing || is_cancelling" :opacity="100">

            <template v-if="!has_error">
                <OrderHeader v-if="order" :order="order"/>
                <OrderInfo :order="order"
                           :crm_url="crmUrl"
                           :debug="debug"
                />
                <OrderContacts :order="order"/>
                <OrderActions :order="order" :is_checking="is_checking" @cancel="cancel" @checkout="checkout"/>
            </template>

            <template v-if="has_error">
                <CheckoutMessage>
                    <div>{{ error_message }}</div>
                    <a class="ap-checkout-link" :href="back_link" v-if="back_link">Вернуться к подбору билетов</a>
                    <CheckoutButton @clicked="close" v-else style="margin-top: 20px;">Закрыть</CheckoutButton>
                </CheckoutMessage>
            </template>
        </CheckoutLoadingProgress>
    </div>
</template>

<script>
import OrderHeader from "@/Pages/Checkout/OrderHeader";
import OrderInfo from "@/Pages/Checkout/OrderInfo";
import OrderContacts from "@/Pages/Checkout/OrderContacts";
import CheckoutLoadingProgress from "@/Pages/Checkout/Components/CheckoutLoadingProgress";
import CheckoutMessage from "@/Pages/Checkout/Components/CheckoutMessage";
import OrderActions from "@/Pages/Checkout/OrderActions";
import CheckoutButton from "@/Pages/Checkout/Components/CheckoutButton";

export default {
    props: {
        crm_url: {type: String, default: 'https://lk.excurr.ru'},
        debug: {type: Boolean, default: false},
    },

    components: {
        CheckoutButton,
        OrderActions,
        CheckoutMessage,
        CheckoutLoadingProgress,
        OrderContacts,
        OrderInfo,
        OrderHeader,
    },

    data: () => ({
        crm_url_override: null,
        processing: true,
        is_cancelling: false,
        is_checking: false,

        order: {},
        payment: {},

        has_error: false,
        error_message: null,
        back_link: null,
    }),

    created() {
        // get config defined outside
        const configElement = document.getElementById('ap-checkout-config');
        const config = configElement !== null ? JSON.parse(configElement.innerHTML) : null;
        if (config !== null) {
            this.crm_url_override = config['crm_url_override'];
        }

        let secret = localStorage.getItem('ap-checkout-order-secret');

        this.init(secret);
    },

    computed: {
        crmUrl() {
            return this.crm_url_override ? this.crm_url_override : this.crm_url;
        },
    },

    methods: {
        url(path) {
            return this.crmUrl + path + (this.debug ? '?XDEBUG_SESSION_START=PHPSTORM' : '');
        },

        init(secret, force = false) {
            const urlParams = new URLSearchParams(window.location.search);
            let parameters = {};
            if (urlParams.has('order')) {
                parameters['order'] = urlParams.get('order');
            } else if (secret !== null) {
                parameters['order'] = secret;
            }
            if (urlParams.has('status') && !force) {
                parameters['status'] = urlParams.get('status');
            }

            this.processing = true;

            axios.post(this.url('/checkout/handle'), parameters)
                .then(response => {
                    if (response.data.payload && response.data.payload['is_ordered']) {
                        localStorage.removeItem('ap-checkout-back-link');
                        localStorage.removeItem('ap-checkout-order-id');
                        localStorage.removeItem('ap-checkout-order-secret');
                        window.location.href = response.data.payload['back_link'];
                    } else {
                        this.order = response.data['order'];
                        this.payment = response.data['payment'];
                        this.processing = false;
                        if (typeof this.order !== "undefined" && this.order !== null) {
                            if (this.order['back_link']) {
                                localStorage.setItem('ap-checkout-back-link', this.order['back_link']);
                            }
                            if (this.order['id']) {
                                localStorage.setItem('ap-checkout-order-id', this.order['id']);
                            }
                            if (this.order['secret']) {
                                localStorage.setItem('ap-checkout-order-secret', this.order['secret']);
                            }
                        }
                    }
                })
                .catch(error => {
                    this.error_message = error.response.data['message'];
                    this.has_error = true;
                    if (error.response.status === 301) {
                        window.location.href = error.response.data['to'];
                    } else {
                        this.processing = false;
                    }
                    this.back_link = typeof error.response.data.payload['back_link'] !== "undefined" ? error.response.data.payload['back_link'] : null;
                });
        },

        cancel() {
            this.is_cancelling = true;
            axios.post(this.url('/checkout/cancel'), {secret: this.order['secret']})
                .then(response => {
                    localStorage.removeItem('ap-checkout-back-link');
                    const back_link = response.data.payload['back_link'];
                    setTimeout(() => {
                        window.location.href = back_link;
                    }, 100);
                })
                .catch(error => {
                    this.error_message = error.response.data['message'];
                    this.back_link = error.response.data.payload['back_link'];
                    this.has_error = true;
                    this.is_cancelling = false;
                });
        },

        close() {
            let secret = localStorage.getItem('ap-checkout-order-secret');
            this.error_message = null;
            this.has_error = false;
            this.init(secret, true);
        },

        checkout() {
            this.is_checking = true;
            axios.post(this.url('/checkout/pay'), {secret: this.order['secret']})
                .then(response => {
                    const form_url = response.data.payload['form_url'];
                    setTimeout(() => {
                        window.location.href = form_url;
                    }, 100);
                })
                .catch(error => {
                    this.error_message = error.response.data['message'];
                    this.back_link = error.response.data.payload['back_link'];
                    this.has_error = true;
                })
                .finally(() => {
                    this.is_checking = false;
                });
        },
    }
}
</script>

<style lang="scss" scoped>
@import "../Pages/Checkout/variables";

.ap-checkout-link {
    font-size: 16px;
    font-family: $checkout_font;
    color: $checkout_link_color;
    cursor: pointer;
    text-decoration: underline;
    margin: 20px auto 0;
    display: inline-block;
}
</style>
