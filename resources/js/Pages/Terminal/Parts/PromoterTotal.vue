<template>
    <div>
        <GuiContainer w-50 mt-30>
            Смена открыта: {{data.open_shift.start_at}} -

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


export default {
    props: {
        partnerId: {type: Number, required: true},
        data: {type: Object},
    },

    emits: ['update'],

    components: {
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
                                this.info.load({id: this.orderId});
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
