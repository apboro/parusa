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
    <div class="ap-showcase__contacts">
        <div class="ap-showcase__contacts-item">
            <span class="ap-showcase__contacts-item-description">Введите ваше имя <sup>*</sup></span>
            <ShowcaseInputString :name="'name'" v-model="name" :autocomplete="'given-name'" :placeholder="'Иван'"
                                 @change="this.showcase3Store.contactInfo.name = name"/>
        </div>
        <div class="ap-showcase__contacts-item">
            <span class="ap-showcase__contacts-item-description">Введите вашу почту <sup>*</sup></span>
            <ShowcaseInputString :name="'email'" v-model="email" :autocomplexte="'email'" :placeholder="'@mail.ru'"
                                 @change="this.showcase3Store.contactInfo.email = email"/>
        </div>
        <div class="ap-showcase__contacts-item">
            <span class="ap-showcase__contacts-item-description">Введите ваш телефон <sup class="ap-showcase__sup">*</sup></span>
            <ShowcaseInputPhone :name="'phone'" v-model="phone" :autocomplete="'tel'" :placeholder="'+7 (999) 999-99-99'"
                                @change="this.showcase3Store.contactInfo.phone = phone"/>
        </div>
    </div>
</template>

<style scoped lang="scss">
    .ap-showcase__contacts {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        gap: 30px;

        @media screen and (max-width: 769px) {
            gap: 20px;
        }

        &-item {
            width: 276px;

            @media screen and (max-width: 769px) {
                width: 100%;
            }

            &-description {
                font-family: Gilroy;
                font-size: 14px;
                font-weight: 400;
                display: inline-block;
                padding-bottom: 5px;

                sup {
                    vertical-align: text-bottom;
                    color: #E83B4E;
                }
            }
        }
    }
</style>
