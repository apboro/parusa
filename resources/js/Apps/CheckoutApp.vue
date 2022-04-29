<template>
    <div class="ap-checkout">
        <CheckoutLoadingProgress :loading="processing" :opacity="100">

            <template v-if="!has_error">
                <OrderHeader v-if="order" :order="order"/>
                <OrderInfo :order="order"/>
                <OrderContacts :order="order"/>
                <OrderActions :order="order"/>
            </template>

            <template v-if="has_error">
                <CheckoutMessage>
                    <div>{{ error_message }}</div>
                    <a :href="back_link" v-if="back_link">Вернуться к оформлению</a>
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

export default {
    props: {
        crm_url: {type: String, default: 'https://cp.parus-a.ru'},
        debug: {type: Boolean, default: false},
    },

    components: {
        OrderActions,
        CheckoutMessage,
        CheckoutLoadingProgress,
        OrderContacts,
        OrderInfo,
        OrderHeader,
    },

    data: () => ({
        processing: true,

        order: {},
        payment: {},

        has_error: false,
        error_message: null,
        back_link: null,
    }),

    created() {
        this.init();
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
            }

            this.processing = true;

            axios.post(this.url('/checkout/handle'), parameters)
                .then(response => {
                    this.order = response.data['order'];
                    this.payment = response.data['payment'];
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
        //checkout() {
        //    this.processing = true;
        //    axios.post(this.url('/checkout/pay'), {order: this.order_encrypted})
        //        .then(() => {
        //            this.$refs.form.submit();
        //        })
        //        .catch(error => {
        //            this.error_message = error.response.data['message'];
        //            this.back_link = error.response.data.payload['backlink'];
        //            this.has_error = true;
        //            this.processing = false;
        //        })
        //},
    }
}
</script>
