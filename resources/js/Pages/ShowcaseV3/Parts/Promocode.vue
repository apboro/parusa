<script>
import ShowcaseButton from "@/Pages/Showcase/Components/ShowcaseButton.vue";
import ShowcaseInputString from "@/Pages/Showcase/Components/ShowcaseInputString.vue";
import {useShowcase3Store} from "@/Stores/showcase3-store";
import { mapStores} from "pinia";

export default {
    name: "Promocode",
    props: {
        message: String
    },
    components: {ShowcaseInputString, ShowcaseButton},
    emits: ['use'],
    data: () => ({
        promocode: null,
    }),
    computed: {
        ...mapStores(useShowcase3Store),
    },
}
</script>

<template>
    <div class="ap-showcase__promocode">
        <span class="ap-showcase__title">Введите промокод (при наличии)</span>
        <div class="ap-showcase__promocode-input">
            <div class="ap-showcase__promocode-item">
                <ShowcaseInputString :name="'promocode'" v-model="promocode" :hide-title="true"
                                     placeholder="Промокод" @change="this.showcase3Store.promocode = promocode"/>
                <span v-if="message" class="ap-showcase__contacts-item-description">{{ message }}</span>
            </div>
            <div class="ap-showcase__promocode-button">
                <ShowcaseButton @clicked="$emit('use')" :disabled="this.promocode === null">Применить
                </ShowcaseButton>
            </div>
        </div>
<!--        <div class="ap-showcase__promocode-discount">-->
<!--            Скидка по промокоду: <span class="ap-showcase__promocode-discount-value">{{ this.showcase3Store.discount.discounted}} руб.</span>-->
<!--        </div>-->
    </div>
</template>

<style scoped lang="scss">
    .ap-showcase__promocode {
        width: 276px;

        @media screen and (max-width: 769px) {
            width: 100%;

            &-item {
                flex-grow: 1;
            }
        }

        .ap-showcase__title {
            font-family: Gilroy;
            font-size: 14px;
            font-weight: 400;
            display: inline-block;
            padding-bottom: 5px;
        }

        &-input {
            display: flex;
            gap: 10px;
        }
    }
</style>
