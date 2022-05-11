<template>
    <div class="ap-final-message">
        <div class="ap-final-message__title" v-if="orderId">Заказ №{{ orderId }}</div>

        <template v-if="status === 'expired'">
            <div class="ap-final-message__status">
                Время, отведенное на оплату заказа, закончилось. Заказ расформирован.
            </div>
            <div class="ap-final-message__buttons">
                <ShowcaseButton @click="close">Закрыть</ShowcaseButton>
            </div>
        </template>

        <template v-if="status === 'payed'">
            <div class="ap-final-message__status">
                Заказ оплачен. Билеты высланы на электронную почту.
            </div>
            <div class="ap-final-message__buttons">
                <ShowcaseButton @click="close">Закрыть</ShowcaseButton>
            </div>
        </template>

        <template v-if="status === 'creating' || status === 'paying'">
            <ShowcaseLoadingProgress :loading="cancelling">
                <div class="ap-final-message__status">
                    Заказ создан. Время на оплату заказа ограничено: {{ remind }}
                </div>
                <div class="ap-final-message__status">
                    Для оплаты заказа перейдите на <a class="ap-final-message__status-link" :href="orderData['payment_page']">страницу оплаты</a>.
                </div>
                <div class="ap-final-message__buttons">
                    <ShowcaseButton color="light" @click="cancel">Отменить заказ</ShowcaseButton>
                </div>
            </ShowcaseLoadingProgress>
        </template>


        <!--        <div>Билеты отправлены Вам на почту, указанную при оформлении.</div>-->
        <!--        <br/>-->
        <!--        <div>Инструкции, если клиент не получил билеты.</div>-->
        <!--        <br/>-->

    </div>
</template>

<script>
import ShowcaseLoadingProgress from "@/Pages/Showcase/Components/ShowcaseLoadingProgress";
import ShowcaseMessage from "@/Pages/Showcase/Components/ShowcaseMessage";
import ShowcaseButton from "@/Pages/Showcase/Components/ShowcaseButton";

export default {
    props: {
        isLoading: {type: Boolean, default: false},
        orderData: {type: Object, default: null},
        secret: {type: String, default: null},
        crm_url: {type: String, default: 'https://cp.parus-a.ru'},
        debug: {type: Boolean, default: false},
    },

    emits: ['close'],

    components: {ShowcaseButton, ShowcaseMessage, ShowcaseLoadingProgress},

    computed: {
        orderId() {
            return (this.orderData !== null && typeof this.orderData['order_id'] !== "undefined") ? this.orderData['order_id'] : null;
        },
        status() {
            if (this.orderData === null) {
                return null;
            }
            if (typeof this.orderData['is_payed'] !== "undefined" && this.orderData['is_payed']) {
                return 'payed';
            }
            if (typeof this.orderData['is_actual'] !== "undefined" && this.orderData['is_actual'] === false || this.expired_internal) {
                return 'expired';
            }
            if (typeof this.orderData['is_paying'] !== "undefined" && this.orderData['is_paying']) {
                return 'paying';
            }
            if (typeof this.orderData['is_created'] !== "undefined" && this.orderData['is_created']) {
                return 'creating';
            }
            return null;
        },
        remind() {
            if (this.lifetime === null) {
                return null;
            }
            const minutes = Math.trunc(this.lifetime / 60);
            const seconds = Math.trunc(this.lifetime - minutes * 60);

            return minutes + ':' + (String(seconds).length === 1 ? '0' + seconds : seconds);
        },
    },

    watch: {
        orderData(value) {
            this.lifetime = typeof value['lifetime'] !== "undefined" ? value['lifetime'] : null;
            if (this.lifetime_timer) {
                clearInterval(this.lifetime_timer);
            }
            if (this.lifetime) {
                this.lifetime_timer = setInterval(this.updateLifetime, 1000);
            }
        },
    },

    data: () => ({
        lifetime: null,
        lifetime_timer: null,
        expired_internal: false,
        cancelling: false,
    }),

    methods: {
        url(path) {
            return this.crm_url + path + (this.debug ? '?XDEBUG_SESSION_START=PHPSTORM' : '');
        },
        updateLifetime() {
            if (this.lifetime === null) {
                return;
            }
            if (this.lifetime === 0) {
                this.expired_internal = true;
            }
            this.lifetime -= 1;
        },
        close() {
            const url = new URL(window.location.href);
            if (url.searchParams.has('status')) {
                url.searchParams.delete('status');
                window.history.replaceState({}, '', url.toString());
            }
            this.$emit('close');
        },
        cancel() {
            this.cancelling = true;
            axios.post(this.url('/showcase/order/cancel'), {secret: this.secret})
                .then(() => {
                    this.$emit('close');
                })
                .catch(error => {
                    console.log(error.response['message']);
                })
                .finally(() => {
                    this.cancelling = false;
                });
        },
    }
}
</script>

<style lang="scss" scoped>
@import "variables";

.ap-final-message {
    &__title {
        font-family: $showcase_font;
        margin: 0;
        font-size: 24px;
        color: $showcase_link_color;
        font-weight: bold;
        text-align: center;
    }

    &__status {
        font-family: $showcase_font;
        margin: 30px 0;
        font-size: 16px;
        font-weight: normal;
        text-align: center;
        color: $showcase_text_color;

        &-link {
            color: $showcase_link_color !important;
            text-decoration: underline !important;
        }
    }

    &__buttons {
        text-align: center;
    }
}

</style>
