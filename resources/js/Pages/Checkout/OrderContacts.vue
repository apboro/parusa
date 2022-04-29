<template>
    <div class="ap-checkout__title">Контактные данные</div>
    <div class="ap-checkout__disclaimer">Имя: {{ order['name'] }}</div>
    <div class="ap-checkout__disclaimer">Email: {{ order['email'] }}</div>
    <div class="ap-checkout__disclaimer">Телефон: {{ order['phone'] }}</div>
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

.ap-checkout__title {
    font-family: $checkout_font;
    margin: 10px 0;
    font-size: 20px;
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
