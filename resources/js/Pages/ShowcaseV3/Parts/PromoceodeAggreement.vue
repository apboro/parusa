<script>
import ShowcaseFieldWrapper from "@/Pages/Showcase/Components/Helpers/ShowcaseFieldWrapper.vue";
import ShowcaseInputCheckbox from "@/Pages/Showcase/Components/ShowcaseInputCheckbox.vue";

export default {
    name: "PromoceodeAggreement",
    components: {ShowcaseInputCheckbox, ShowcaseFieldWrapper},
    props: {
        status: true,
        agreement_valid: true,
        agreement_promocode_valid: true,
    },
    data: () => ({
        agree_promocode: true,
    })
}
</script>

<template>
    <div class="ap-showcase__agreement" v-if="status">
        <ShowcaseFieldWrapper :hide-title="true" :valid="agreement_promocode_valid"
                              :errors="['Необходимо принять условия использования промокода']">
            <ShowcaseInputCheckbox v-model="agree_promocode" :small="true">
                Билеты, купленные с применением промокода, возврату не подлежат
            </ShowcaseInputCheckbox>
        </ShowcaseFieldWrapper>
    </div>
</template>

<style scoped lang="scss">

</style>
