<script>
import PersonalDataInfo from "@/Pages/Showcase/Parts/PersonalDataInfo.vue";
import ShowcaseFieldWrapper from "@/Pages/Showcase/Components/Helpers/ShowcaseFieldWrapper.vue";
import OfferInfo from "@/Pages/Showcase/Parts/OfferInfo.vue";
import ShowcaseInputCheckbox from "@/Pages/Showcase/Components/ShowcaseInputCheckbox.vue";

export default {
    name: "Aggreement",
    components: {ShowcaseInputCheckbox, OfferInfo, ShowcaseFieldWrapper, PersonalDataInfo},
    props: {
        crm_url: {type: String, required: true},
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
                   :crm_url="crm_url"
                   :debug="debug"
                   :session="session"
        />
        <PersonalDataInfo ref="personal"
                          :crm_url="crm_url"
                          :debug="debug"
                          :session="session"
        />
    </div>
</template>

<style scoped lang="scss">

</style>
