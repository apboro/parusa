<template>
    <LayoutPage :title="title" :loading="processing">
        <template v-if=""
        <template v-else>
            <GuiMessage>Заказ закрыт</GuiMessage>
            <GuiContainer mt-30>
                <GuiButton @click="back">Вернуться к подбору билетов</GuiButton>
            </GuiContainer>
        </template>
    </LayoutPage>
</template>

<script>
import LayoutPage from "@/Components/Layout/LayoutPage";
import data from "@/Core/Data";
import GuiMessage from "@/Components/GUI/GuiMessage";
import GuiContainer from "@/Components/GUI/GuiContainer";
import GuiButton from "@/Components/GUI/GuiButton";

export default {
    components: {
        GuiButton,
        GuiContainer,
        GuiMessage,
        LayoutPage,
    },

    data: () => ({
        order: data('/api/order/terminal/current'),
    }),

    computed: {
        processing() {
            return this.order.is_loading && true;
        },
        title() {
            return 'Заказ №' + (this.order.is_loaded ? this.order.data['id'] : '...');
        }
    },

    created() {
        this.order.load();
    },

    methods: {
        back() {
            this.$router.push({name: 'tickets-select'});
        },
    }
}
</script>

