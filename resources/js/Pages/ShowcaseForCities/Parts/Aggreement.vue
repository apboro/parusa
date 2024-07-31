<script>
import PersonalDataInfo from "@/Pages/ShowcaseForCities/Parts/PersonalDataInfo.vue";
import ShowcaseFieldWrapper from "@/Pages/Showcase/Components/Helpers/ShowcaseFieldWrapper.vue";
import OfferInfo from "@/Pages/ShowcaseForCities/Parts/OfferInfo.vue";
import ShowcaseInputCheckbox from "@/Pages/Showcase/Components/ShowcaseInputCheckbox.vue";

export default {
    name: "Aggreement",
    components: {ShowcaseInputCheckbox, OfferInfo, ShowcaseFieldWrapper, PersonalDataInfo},
    props: {
        debug: {type: Boolean, default: false},
        session: {type: String, default: null},
    },
    data: () => ({
        agreement: true,
        agreement_valid: true,
        agreement_promocode: true,
        agreement_promocode_valid: true,
    }),
    computed: {
        agree: {
            get() {
                return this.agreement;
            },
            set(value) {
                this.agreement = value;
                this.agreement_valid = true;
            }
        },
    }
}
</script>

<template>
    <div class="ap-showcase__agreement">
        <ShowcaseFieldWrapper :hide-title="true" :valid="agreement_promocode_valid"
                              :errors="['Необходимо принять условия оферты на оказание услуг и дать своё согласие на обработку персональных данных']">
            <ShowcaseInputCheckbox v-model="agree" :small="true">
                Я принимаю условия <span class="ap-showcase__link" @click="">Оферты на оказание услуг</span>
                и даю своё <span class="ap-showcase__link" @click="">согласие на обработку
                        персональных данных</span>
            </ShowcaseInputCheckbox>
        </ShowcaseFieldWrapper>

        <OfferInfo ref="offer"
                   :debug="debug"
                   :session="session"
        />
        <PersonalDataInfo ref="personal"
                          :debug="debug"
                          :session="session"
        />
    </div>
</template>

<style scoped lang="scss">
    .ap-showcase__agreement {
        font-family: Gilroy;
        font-size: 10px;
        font-weight: 400;
        color: #0E0E0E;

        .ap-showcase__link {
            text-decoration: underline;
        }
    }
</style>
