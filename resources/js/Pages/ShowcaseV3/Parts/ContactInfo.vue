<script>
import ShowcaseInputPhone from "@/Pages/Showcase/Components/ShowcaseInputPhone.vue";
import ShowcaseInputString from "@/Pages/Showcase/Components/ShowcaseInputString.vue";

export default {
    name: "ContactInfo",
    components: {ShowcaseInputString, ShowcaseInputPhone}
}
</script>

<template>
    <div class="ap-showcase__title">Контактные данные</div>

    <div class="ap-showcase__text">Контактные данные необходимы на случай отмены рейса, а также для отправки
        билетов. Данные не передаются третьим лицам.
    </div>

    <div class="ap-showcase__contacts">
        <div class="ap-showcase__contacts-item">
            <ShowcaseInputString :name="'name'" :autocomplete="'given-name'"/>
        </div>
        <div class="ap-showcase__contacts-item">
            <ShowcaseInputPhone :name="'phone'" :autocomplete="'tel'"/>
            <span class="ap-showcase__contacts-item-description">Необходим на случай отмены рейса.</span>
        </div>
        <div class="ap-showcase__contacts-item">
            <ShowcaseInputString :name="'email'" :autocomplete="'email'"/>
            <span
                class="ap-showcase__contacts-item-description">На этот адрес будет выслан электронный билет.</span>
        </div>
    </div>
</template>

<style scoped lang="scss">

</style>
