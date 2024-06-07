<template>
    <PopUp :title="'Выплата за смену'"
           :manual="true"
           :resolving="resolved"
           :buttons="[
               {result: 'payout', caption: 'Выплатить', color: 'green', disabled: (!sumToPay && !payTaxi) || (sumToPay > totalToPay && !payTaxi)},
               {result: 'cancel', caption: 'Отмена'},
           ]"
           ref="popup"
    >
        <GuiText mb-10> Итого к оплате за смену: {{ totalToPay }} руб.</GuiText>
        <InputNumber :placeholder="'Введите сумму для выплаты'" v-model="sumToPay"/>
        <p v-if="sumToPay > totalToPay" style="color: red">Максимум {{ totalToPay }} руб.</p>
        <br>
        <GuiText mb-10> На такси:</GuiText>
        <InputNumber :placeholder="'Введите сумму'" v-model="payTaxi"/>
        <p v-if="payTaxi > 500" style="color: red">Максимум 500 руб.</p>
    </PopUp>
</template>

<script>
import FormNumber from "@/Components/Form/FormNumber";
import PopUp from "@/Components/PopUp";
import InputNumber from "@/Components/Inputs/InputNumber.vue";
import GuiText from "@/Components/GUI/GuiText.vue";

export default {
    components: {
        GuiText,
        InputNumber,
        PopUp,
        FormNumber,
    },

    emits: ['made_payout'],

    props: {
        partnerId: Number,
        totalToPay: Number,
    },

    data: () => ({
        sumToPay: null,
        payTaxi: null,
    }),

    methods: {
        show() {
            this.$refs.popup.show();
        },

        resolved(result) {
            if (['payout'].indexOf(result) === -1) {
                this.$refs.popup.hide();
                return true;
            }
            this.$refs.popup.process(true);
            axios.post('/api/terminals/promoters/pay_work_shift', {
                sumToPay: this.sumToPay,
                totalToPay: this.totalToPay,
                partnerId: this.partnerId,
                payTaxi: this.payTaxi
            }).then((response) => {
                this.$refs.popup.hide();
                this.$emit('made_payout', {sumToPay: this.sumToPay, payTaxi: this.payTaxi});
                this.sumToPay = null;
                this.payTaxi = null;
                this.$toast.success(response.data['message']);
            }).catch(error => {
                this.$toast.error(error.response.data['message']);
            }).finally(() => {
                this.$refs.popup.process(false);
            });

            return false;
        }
    }
}
</script>

<style lang="scss" scoped>
$project_font: -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica Neue, Arial, Noto Sans, sans-serif, Apple Color Emoji, Segoe UI Emoji, Segoe UI Symbol, Noto Color Emoji !default;
$base_black_color: #1e1e1e !default;

.tickets-select-table {
    font-size: 14px;
    font-family: $project_font;
    border-collapse: collapse;
    margin: 10px 0 0;

    & thead {
        color: #424242;

        & td {
            vertical-align: middle;
        }
    }

    & th {
        text-align: left;
        padding: 10px;
        cursor: default;
    }

    & td {
        vertical-align: middle;
        color: $base_black_color;
        padding: 0 10px;
        cursor: default;
        white-space: nowrap;
    }

    &__total {
        font-size: 16px;
        font-weight: bold;
        padding-top: 15px !important;
    }

    & .input-field {
        width: 140px;
    }

    &:deep(.input-number__input) {
        text-align: center;
    }

    & .input-field__errors-error {
        font-size: 12px;
    }
}
</style>
