<template>
    <div class="ap-showcase__checkout">
        <div class="ap-showcase__checkout-total">
            Итого к оплате:
            <span class="ap-showcase__checkout-total-value">{{ order['total'] }} руб.</span>
        </div>
        <div class="ap-showcase__checkout-button ap-showcase__checkout-button-cancel">
            <CheckoutButton color="light" @clicked="$emit('cancel')" :disabled="!order['secret']">Отменить заказ</CheckoutButton>
        </div>
        <div class="ap-showcase__checkout-button ap-showcase__checkout-button-ok">
            <CheckoutButton @clicked="$emit('checkout')" :disabled="!order['secret']">Оплатить</CheckoutButton>
        </div>
    </div>
</template>

<script>
import CheckoutButton from "@/Pages/Checkout/Components/CheckoutButton";

export default {
    emits: ['checkout', 'cancel'],

    components: {CheckoutButton},

    props: {
        order: {type: Object, default: null},
    },
}
</script>

<style lang="scss" scoped>
@import "variables";

.ap-showcase__checkout {
    display: flex;
    flex-direction: row;
    margin: 20px 0;

    &-total {
        font-family: $checkout_font;
        font-size: 16px;
        flex-grow: 1;
        background-color: #f1f1f1;
        display: flex;
        align-items: center;
        height: $checkout_size_unit;
        box-sizing: border-box;
        padding: 0 15px;

        &-value {
            margin-left: 15px;
            font-size: 24px;
            color: $checkout_primary_color;
        }
    }

    &-button {
        margin-left: 10px;
        flex-grow: 0;
    }
}

@media screen and (max-width: 670px) {
    .ap-showcase__checkout {
        background-color: #f1f1f1;
        flex-direction: column;
        padding: 10px 0;

        &-total {
            justify-content: center;
            margin-bottom: 10px;
            order: 1;
        }

        &-button {
            width: 100%;
            margin: 0;
            text-align: center;

            &-cancel {
                order: 3;
            }

            &-ok {
                order: 2;
                margin-bottom: 10px;
            }

            & .ap-button {
                width: calc(100% - 20px);
            }
        }
    }
}
</style>
