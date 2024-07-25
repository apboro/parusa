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
                            <s>{{ this.showcase3Store.getPrice }}</s> {{ discount_price }} ₽
                        </template>
                        <template v-else>
                            {{ this.showcase3Store.getPrice }} ₽
                        </template>
                    </span>
            <span v-else class="ap-showcase__checkout-not-selected">В заказе отсутствуют билеты</span>
        </div>
        <div class="ap-showcase__checkout-button">
            <ShowcaseButton v-if="showcase3Store.trip.trip_with_seats" @clicked="$emit('pay')"
                            :disabled="this.showcase3Store.tickets.length < 1">Оплатить экскурсию
            </ShowcaseButton>
            <ShowcaseButton v-else @clicked="$emit('pay')" :disabled="this.showcase3Store.getPrice === null">Оплатить экскурсию</ShowcaseButton>
        </div>
    </div>
</template>

<style scoped lang="scss">
    .ap-showcase__checkout-total {
        color: #0E0E0E;
        font-family: Gilroy;
        font-size: 20px;
        font-weight: 500;
        margin-bottom: 10px;

        &-value {
            color: #E83B4E;
            font-family: Segoe UI;
            font-size: 24px;
            font-weight: 600;
        }

        @media screen and (max-width: 769px) {
            font-size: 16px;
        }
    }

    .ap-showcase__checkout-button {

        .ap-button {
            width: 100%;
        }
    }
</style>
