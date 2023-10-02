<template>
    <div>
        <GuiContainer w-50 mt-30 v-if="!data.open_shift" >
            <GuiMessage border>Смена не открыта</GuiMessage>
        </GuiContainer>

        <GuiContainer w-50 mt-30 v-else>
            <GuiText>Смена открыта: {{data.open_shift.start_at}}</GuiText>
            <GuiText>Оплата за выход: {{data.payForOut}} руб.</GuiText>
            <GuiText>Оплата за время: {{data.payForTime}} руб.</GuiText>
            <GuiText>Оплата за продажи: {{data.payCommission}} руб.</GuiText>
        </GuiContainer>

        <GuiContainer w-100 mt-20>
            <GuiButton v-if="data.open_shift" @click="closeShift" >Закрыть смену</GuiButton>
        </GuiContainer>

    </div>
</template>

<script>
import GuiContainer from "@/Components/GUI/GuiContainer";
import GuiValue from "@/Components/GUI/GuiValue";
import GuiValueArea from "@/Components/GUI/GuiValueArea";
import GuiButton from "@/Components/GUI/GuiButton";
import GuiActivityIndicator from "@/Components/GUI/GuiActivityIndicator";
import GuiHint from "@/Components/GUI/GuiHint";
import GuiFilesList from "@/Components/GUI/GuiFilesList";
import GuiMessage from "@/Components/GUI/GuiMessage.vue";
import GuiText from "@/Components/GUI/GuiText.vue";


export default {
    props: {
        partnerId: {type: Number, required: true},
        data: {type: Object},
    },

    emits: ['update'],

    components: {
        GuiText,
        GuiMessage,
        GuiFilesList,
        GuiHint,
        GuiActivityIndicator,
        GuiButton,
        GuiValueArea,
        GuiValue,
        GuiContainer
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
        }
    }


}
</script>
