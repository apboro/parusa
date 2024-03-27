<template>
    <div>
        <GuiContainer w-50 mt-30 v-if="!data.open_shift" >
            <GuiMessage border>Смена не открыта</GuiMessage>
        </GuiContainer>

        <GuiContainer w-50 mt-30 v-else>
            <GuiValue :title="'Смена открыта:'"> {{data.open_shift.start_at}}</GuiValue>
            <GuiValue :title="'Тариф:'"> {{data.open_shift.tariff.commission}} %
                <span v-if="data.open_shift.commission_delta !== 0">
                    ({{data.open_shift.commission_delta > 0 ? '+'+data.open_shift.commission_delta : data.open_shift.commission_delta}}%)
                </span>
            </GuiValue>
            <GuiValue v-if="data.payForOut" :title="'Оплата за выход:'"> {{data.payForOut}} руб.</GuiValue>
            <GuiValue v-if="data.payForTime" :title="'Оплата за время:'"> {{data.payForTime}} руб.</GuiValue>
            <GuiValue v-if="data.payCommission" :title="'Оплата за продажи:'"> {{data.payCommission}} руб.</GuiValue>
            <GuiValue v-if="data.balance" :title="'Перенос с прошлой смены:'"> {{data.balance}} руб.</GuiValue>
            <br>
            <GuiValue v-if="paid_out" :title="'Выплачено:'"> {{paid_out}} руб.</GuiValue>
            <GuiValue :title="'Итого к оплате:'"> {{totalToPay}} руб.</GuiValue>
        </GuiContainer>

        <GuiContainer v-if="data.open_shift" w-100 mt-20>
            <GuiButton color="red" @click="closeShift" >Закрыть смену</GuiButton>
            <GuiButton color="green" @click="payShift" >Оплата</GuiButton>
            <GuiButton color="blue" @click="printPayout" >Печать расписки</GuiButton>
        </GuiContainer>

        <PayoutShift ref="payout_popup" :totalToPay="totalToPay" :partnerId="partnerId" @made_payout="update"/>

    </div>
</template>

<script>
import GuiContainer from "@/Components/GUI/GuiContainer";
import GuiValueArea from "@/Components/GUI/GuiValueArea";
import GuiButton from "@/Components/GUI/GuiButton";
import GuiActivityIndicator from "@/Components/GUI/GuiActivityIndicator";
import GuiHint from "@/Components/GUI/GuiHint";
import GuiFilesList from "@/Components/GUI/GuiFilesList";
import GuiMessage from "@/Components/GUI/GuiMessage.vue";
import GuiValue from "@/Components/GUI/GuiValue.vue";
import PayoutShift from "@/Pages/Terminal/Parts/PayoutShift.vue";
import printJS from "print-js";


export default {
    props: {
        partnerId: {type: Number, required: true},
        data: {type: Object},
    },
    emits: ['update'],

    data: () => ({
       payout: null
    }),

    components: {
        PayoutShift,
        GuiValue,
        GuiMessage,
        GuiFilesList,
        GuiHint,
        GuiActivityIndicator,
        GuiButton,
        GuiValueArea,
        GuiContainer
    },

    computed: {
        totalToPay(){
            return this.data.payForOut + this.data.payForTime + this.data.payCommission + this.data.balance - this.data.paid_out - this.payout;
        },
        paid_out(){
            return this.data.paid_out + this.payout;
        }
    },

    methods: {
        closeShift() {
            this.$dialog.show('Закрыть смену?', 'question', 'orange', [
                this.$dialog.button('ok', 'Продолжить', 'orange'),
                this.$dialog.button('cancel', 'Отмена'),
            ], 'center')
                .then((result) => {
                    if (result === 'ok') {
                        axios.post('/api/terminals/promoters/close_work_shift', {partnerId: this.partnerId})
                            .then((response) => {
                                this.$toast.success(response.data['message']);
                                this.data.open_shift = null;
                            })
                            .catch(error => {
                                this.$toast.error(error.response.data['message']);
                            })
                    }
                });
        },
        payShift(){
            this.$emit('update')
            this.$refs.payout_popup.show()
        },
        update(sumToPay){
            this.payout = sumToPay;
        },
        printPayout(){
            axios.post('/api/terminals/promoters/print_payout', {partnerId: this.partnerId})
                .then(response => {
                    let order = atob(response.data.data['workShift']);
                    let byteNumbers = new Array(order.length);
                    for (let i = 0; i < order.length; i++) {
                        byteNumbers[i] = order.charCodeAt(i);
                    }
                    let byteArray = new Uint8Array(byteNumbers);
                    let blob = new Blob([byteArray], {type: "application/pdf;charset=utf-8"});
                    let pdfUrl = URL.createObjectURL(blob);
                    printJS(pdfUrl);
                })
                .catch(error => {
                    this.$toast.error(error.response.data['message']);
                });
        }
    }
}
</script>
