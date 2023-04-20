<template>
    <LoadingProgress :loading="data.is_loading">
        <GuiHint mx-10 my-15>Тарифы и комиссионное вознаграждение партнёра за продажу услуг компании "Алые Паруса"</GuiHint>
        <GuiHint mx-10 mb-15 mt-25>1. Параметр применяется только на мобильных кассах.</GuiHint>
        <GuiHint mx-10 my-15>
            2. Указанное комиссионное вознаграждение назначается партнёрам вне зависимости от способа продаж. Если для партнёра не прописаны специальные условия, расчёт
            вознаграждения ведётся по этой колонке.
        </GuiHint>

        <template v-for="excursion in data.payload['excursions']" v-if="data.payload['excursions'] && data.payload['excursions'].length > 0">
            <GuiHeading bold mt-30 mb-10>{{ excursion['name'] }}</GuiHeading>
            <GuiContainer pl-10>
                <GuiMessage border mt-15 v-if="currentRate(excursion) === null">Тарифы не заданы</GuiMessage>
                <TicketRate v-else :rate="currentRate(excursion)" :overridable="true">
                    <GuiActionsMenu :title="null">
                        <span class="link" @click="override(currentRate(excursion))">Установить спец. условия</span>
                    </GuiActionsMenu>
                </TicketRate>

                <GuiHeading bold mt-10 text-sm>Будущие тарифы
                    <GuiExpand @expand="excursion['coming'] = $event"/>
                </GuiHeading>
                <template v-if="excursion['coming']">
                    <GuiMessage text-red border mt-5 v-if="comingRates(excursion).length === 0">Будущих тарифов нет</GuiMessage>
                    <TicketRate v-else v-for="rate in comingRates(excursion)" :rate="rate" :overridable="true">
                        <GuiActionsMenu :title="null" v-if="editable">
                            <span class="link" @click="override(rate)">Установить спец. условия</span>
                        </GuiActionsMenu>
                    </TicketRate>
                </template>

                <GuiHeading bold mt-10 text-sm>Прошлые тарифы
                    <GuiExpand @expand="excursion['archive'] = $event"/>
                </GuiHeading>
                <template v-if="excursion['archive']">
                    <GuiMessage border mt-5 v-if="archivedRates(excursion).length === 0">Прошлых тарифов нет</GuiMessage>
                    <TicketRate v-else v-for="rate in archivedRates(excursion)" :rate="rate" :overridable="true"/>
                </template>
            </GuiContainer>
        </template>

        <PartnerTicketRateOverride :partner-id="partnerId" @update="update" ref="override"/>
    </LoadingProgress>
</template>

<script>
import data from "@/Core/Data";
import LoadingProgress from "@/Components/LoadingProgress";
import GuiHint from "@/Components/GUI/GuiHint";
import GuiHeading from "@/Components/GUI/GuiHeading";
import TicketRate from "@/Pages/Parts/Rates/TicketRate";
import GuiMessage from "@/Components/GUI/GuiMessage";
import GuiActionsMenu from "@/Components/GUI/GuiActionsMenu";
import GuiExpand from "@/Components/GUI/GuiExpand";
import GuiContainer from "@/Components/GUI/GuiContainer";
import PartnerTicketRateOverride from "@/Pages/Admin/Partners/Parts/PartnerTicketRateOverride";

export default {
    props: {
        partnerId: {type: Number, required: true},
        editable: {type: Boolean, default: false},
    },

    components: {
        PartnerTicketRateOverride,
        GuiContainer,
        GuiExpand,
        GuiActionsMenu,
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
        this.data.load({archive: true, partner_id: this.partnerId});
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

        comingRates(excursion) {
            if (Object.keys(this.data.data).length === 0) {
                return [];
            }
            return this.data.data.filter(rate => rate['excursion_id'] === excursion['id'] && !rate['current'] && !rate['archive']);
        },

        archivedRates(excursion) {
            if (Object.keys(this.data.data).length === 0) {
                return [];
            }
            return this.data.data.filter(rate => rate['excursion_id'] === excursion['id'] && rate['archive']).reverse();
        },

        override(rate) {
            this.$refs.override.show(rate);
        },

        update(updated) {
            this.data.data.some((rate, key) => {
                if (rate['id'] === updated['id']) {
                    this.data.data[key] = updated;
                    return true;
                }
                return false;
            })
        }
    }
}
</script>
