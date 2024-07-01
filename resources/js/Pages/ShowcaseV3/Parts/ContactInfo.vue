<script>
import ShowcaseInputPhone from "@/Pages/Showcase/Components/ShowcaseInputPhone.vue";
import ShowcaseInputString from "@/Pages/Showcase/Components/ShowcaseInputString.vue";
import {useShowcase3Store} from "@/Stores/showcase3-store";
import { mapStores} from "pinia";

export default {
    name: "ContactInfo",
    components: {ShowcaseInputString, ShowcaseInputPhone},
    data: () => ({
        name: null,
        phone: null,
        email: null,
    }),
    computed: {
        ...mapStores(useShowcase3Store),
    },

}
</script>

<template>
    <div>
        <div class="ap-showcase__title">Укажите ваши контактные данные</div>

        <div class="ap-showcase__text">Контактные данные необходимы на случай отмены рейса, а также для отправки
            билетов. Данные не передаются третьим лицам.
        </div>

        <div class="ap-showcase__contacts">
            <div class="ap-showcase__contacts-item">
                <span class="ap-showcase__contacts-item-description">Введите ваше имя</span>
                <ShowcaseInputString :name="'name'" v-model="name" :autocomplete="'given-name'" :placeholder="'Иван'"
                                     @change="this.showcase3Store.contactInfo.name = name"/>
            </div>
            <div class="ap-showcase__contacts-item">
                <span class="ap-showcase__contacts-item-description">Введите ваш телефон</span>
                <ShowcaseInputPhone :name="'phone'" v-model="phone" :autocomplete="'tel'" :placeholder="'+7 (999) 999-99-99'"
                                    @change="this.showcase3Store.contactInfo.phone = phone"/>
            </div>
            <div class="ap-showcase__contacts-item">
                <span class="ap-showcase__contacts-item-description">Введите вашу почту</span>
                <ShowcaseInputString :name="'email'" v-model="email" :autocomplexte="'email'" :placeholder="'@mail.ru'"
                                     @change="this.showcase3Store.contactInfo.email = email"/>
            </div>
        </div>
    </div>
</template>

<style scoped lang="scss">

</style>
