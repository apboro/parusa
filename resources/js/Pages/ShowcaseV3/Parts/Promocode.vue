<script>
import ShowcaseButton from "@/Pages/Showcase/Components/ShowcaseButton.vue";
import ShowcaseInputString from "@/Pages/Showcase/Components/ShowcaseInputString.vue";

export default {
    name: "Promocode",
    components: {ShowcaseInputString, ShowcaseButton},
    data: () => ({
        message: null,
    })
}
</script>

<template>
    <div class="ap-showcase__promocode">
        <span class="ap-showcase__title">Промокод</span>
        <div class="ap-showcase__promocode-input">
            <div class="ap-showcase__promocode-item">
                <ShowcaseInputString :name="'promocode'" :hide-title="true"
                                     placeholder="Промокод"/>
                <span v-if="message" class="ap-showcase__contacts-item-description">{{ message }}</span>
            </div>
            <div class="ap-showcase__promocode-button">
                <ShowcaseButton @clicked="" :disabled="false">Применить
                </ShowcaseButton>
            </div>
        </div>
        <div class="ap-showcase__promocode-discount">
            Скидка по промокоду: <span class="ap-showcase__promocode-discount-value">{{

                        }} руб.</span>
        </div>
    </div>
</template>

<style scoped lang="scss">

</style>
