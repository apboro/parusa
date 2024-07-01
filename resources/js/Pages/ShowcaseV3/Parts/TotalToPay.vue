<script>
import ShowcaseButton from "@/Pages/Showcase/Components/ShowcaseButton.vue";
import {useShowcase3Store} from "@/Stores/showcase3-store";
import { mapStores} from "pinia";

export default {
    name: "TotalToPay",
    components: {ShowcaseButton},
    emits: ['pay'],
    props:{
        total: null,
        discount_price: null,
        status: true,
        ship_has_scheme: false,
        tickets: Array,
    },
    computed: {
        ...mapStores(useShowcase3Store),
    },
}
</script>

<template>
    <div class="ap-showcase__checkout">
        <div class="ap-showcase__checkout-total">
            Итого к оплате:
            <span v-if="this.showcase3Store.getPrice" class="ap-showcase__checkout-total-value">
                        <template v-if="status">
                            <s>{{ this.showcase3Store.getPrice }}</s> {{ discount_price }} руб.
                        </template>
                        <template v-else>
                            {{ this.showcase3Store.getPrice }} руб.
                        </template>
                    </span>
            <span v-else class="ap-showcase__checkout-not-selected">В заказе отсутствуют билеты</span>
        </div>
        <div class="ap-showcase__checkout-button">
            <ShowcaseButton v-if="showcase3Store.trip.trip_with_seats" @clicked="$emit('pay')"
                            :disabled="this.showcase3Store.tickets.length < 1">Оплатить
            </ShowcaseButton>
            <ShowcaseButton v-else @clicked="$emit('pay')" :disabled="this.showcase3Store.getPrice === null">Оплатить</ShowcaseButton>
        </div>
    </div>
</template>

<style scoped lang="scss">

</style>
