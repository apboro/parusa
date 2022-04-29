<template>
    <div class="ap-checkout__header">Подтверждение заказа №{{ order['id'] }}</div>
    <div class="ap-checkout__disclaimer">Проверьте контактные данные и состав заказа. Если всё в порядке, то заказ можно оплачивать.</div>
    <div class="ap-checkout__disclaimer">Если Вы увидели ошибку, нажмите на кнопку "отменить заказ".</div>
</template>

<script>
export default {
    props: {
        order: {type: Object, default: null},
    },
}
</script>

<style lang="scss" scoped>
@import "variables";

.ap-checkout__header {
    font-family: $checkout_font;
    margin: 0 0 20px 0;
    font-size: 24px;
    color: $checkout_link_color;
    font-weight: bold;
}

.ap-checkout__disclaimer {
    font-family: $checkout_font;
    margin: 0 0 10px 0;
    font-size: 16px;
    color: $checkout_text_color;
    font-weight: normal;
}
</style>
