<script>
import ShowcaseButton from "@/Pages/Showcase/Components/ShowcaseButton.vue";

export default {
    name: "TotalToPay",
    components: {ShowcaseButton},
    props:{
        total: null,
        discount_price: null,
        count: null,
        status: true,
        ship_has_scheme: false,
        tickets: Array,
    }
}
</script>

<template>
    <div class="ap-showcase__checkout">
        <div class="ap-showcase__checkout-total">
            Итого к оплате:
            <span v-if="count > 0" class="ap-showcase__checkout-total-value">
                        <template v-if="status">
                            <s>{{ total }}</s> {{ discount_price }} руб.
                        </template>
                        <template v-else>
                            {{ total }}
                        </template>
                    </span>
            <span v-else class="ap-showcase__checkout-not-selected">В заказе отсутствуют билеты</span>
        </div>
        <div class="ap-showcase__checkout-button">
            <ShowcaseButton v-if="ship_has_scheme" @clicked=""
                            :disabled="tickets.length < 1">Оплатить
            </ShowcaseButton>
            <ShowcaseButton v-else @clicked="" :disabled="count < 1">Оплатить</ShowcaseButton>
        </div>
    </div>
</template>

<style scoped lang="scss">

</style>
