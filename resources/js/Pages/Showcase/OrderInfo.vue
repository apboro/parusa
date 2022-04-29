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
        <template v-if="status === 'creating'">
            <div class="ap-final-message__status">
                Заказ забронирован. Время на оплату заказа ограничено: {{ remind }}
            </div>
            <div class="ap-final-message__status">
                Для оплаты заказа перейдите на <a class="ap-final-message__status-link" :href="orderData['payment_page']">страницу оплаты</a>.
            </div>
            <div class="ap-final-message__buttons">
                <ShowcaseButton color="light" @click="cancel">Отменить заказ</ShowcaseButton>
            </div>
        </template>
        <!--        <div>Билеты отправлены Вам на почту, указанную при оформлении.</div>-->
        <!--        <br/>-->
        <!--        <div>Инструкции, если клиент не получил билеты.</div>-->
        <!--        <br/>-->

    </div>
</template>

<script>

import ShowcaseMessage from "@/Pages/Showcase/Components/ShowcaseMessage";
import ShowcaseButton from "@/Pages/Showcase/Components/ShowcaseButton";

export default {
    props: {
        isLoading: {type: Boolean, default: false},
        orderData: {type: Object, default: null},
    },

    emits: ['close'],

    components: {ShowcaseButton, ShowcaseMessage},

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
    }),

    methods: {
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
            this.$emit('close');
        },
        cancel() {

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
