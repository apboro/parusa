<template>
    <LoadingProgress :loading="data.is_loading">
        <GuiHint mx-10 my-15>Тарифы и комиссионное вознаграждение партнёра за продажу услуг компании "Алые Паруса"</GuiHint>

        <template v-for="excursion in data.payload['excursions']" v-if="data.payload['excursions'] && data.payload['excursions'].length > 0">
            <GuiHeading bold mt-30 mb-10>{{ excursion['name'] }}</GuiHeading>
            <GuiContainer pl-10>
                <GuiMessage border mt-15 v-if="currentRate(excursion) === null">Тарифы не заданы</GuiMessage>
                <TicketRate v-else :rate="currentRate(excursion)"
                            :overridable="true"
                            :hints="false"
                            :min-max="false"
                />
            </GuiContainer>
        </template>
    </LoadingProgress>
</template>

<script>
import data from "@/Core/Data";
import LoadingProgress from "@/Components/LoadingProgress";
import GuiHint from "@/Components/GUI/GuiHint";
import GuiHeading from "@/Components/GUI/GuiHeading";
import TicketRate from "@/Pages/Parts/Rates/TicketRate";
import GuiMessage from "@/Components/GUI/GuiMessage";
import GuiContainer from "@/Components/GUI/GuiContainer";

export default {
    components: {
        GuiContainer,
        GuiMessage,
        TicketRate,
        GuiHeading,
        GuiHint,
        LoadingProgress
    },

    data: () => ({
        data: data('/api/rates'),
    }),

    computed: {
        processing() {
            return this.data.is_loading;
        },
    },

    created() {
        this.data.load({archive: true});
    },

    methods: {
        currentRate(excursion) {
            let current = null;
            if (Object.keys(this.data.data).length > 0) {
                this.data.data.some(rate => {
                    if (rate['excursion_id'] === excursion['id'] && rate['current']) {
                        current = rate;
                        return true;
                    }
                    return false;
                });
            }
            return current;
        },
    }
}
</script>
