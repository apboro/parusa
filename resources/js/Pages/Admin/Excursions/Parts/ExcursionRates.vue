<template>
    <LoadingProgress :loading="data.is_loading">
        <div class="text-right mb-20" v-if="editable">
            <GuiButton @click="create">Добавить тариф</GuiButton>
        </div>

        <GuiHint mx-10 my-15>Указанные тарифы будут применены по умолчанию ко всем продаваемым билетам. Исключение составляют билеты, реализуемые партнёрами по специальным
            условиям.
        </GuiHint>

        <GuiHeading bold mb-10>Действующий тариф</GuiHeading>
        <GuiMessage text-red border mt-5 v-if="currentRate === null">Действующий тариф не задан</GuiMessage>
        <TicketRate v-else :rate="currentRate" :with-site-price="true">
            <GuiActionsMenu :title="null" v-if="editable">
                <span class="link" @click="edit(currentRate)">Редактировать</span>
                <span class="link" @click="createFrom(currentRate)">Копировать тариф</span>
                <span class="link" @click="override(currentRate)">Установить спец. условия</span>
                <span class="link" @click="remove(currentRate)">Удалить</span>
            </GuiActionsMenu>
        </TicketRate>
        <GuiHint mx-10 mb-15 mt-25>
            1. Параметр применяется только на мобильных кассах.
        </GuiHint>
        <GuiHint mx-10 my-15>
            2. Указанное комиссионное вознаграждение назначается партнёрам вне зависимости от способа продаж. Если для партнёра не прописаны специальные условия, расчёт
            вознаграждения ведётся по этой колонке.
        </GuiHint>

        <GuiHeading bold mt-30 mb-10>Будущие тарифы</GuiHeading>
        <GuiMessage text-red border mt-5 v-if="comingRates.length === 0">Будущих тарифов нет</GuiMessage>
        <TicketRate v-else v-for="rate in comingRates" :rate="rate" :with-site-price="true">
            <GuiActionsMenu :title="null" v-if="editable">
                <span class="link" @click="edit(rate)">Редактировать</span>
                <span class="link" @click="createFrom(rate)">Копировать тариф</span>
                <span class="link" @click="remove(rate)">Удалить</span>
            </GuiActionsMenu>
        </TicketRate>

        <GuiHeading bold mt-30 mb-10>Прошлые тарифы
            <GuiExpand @expand="archive = $event"/>
        </GuiHeading>
        <GuiMessage border mt-5 v-if="archive && archivedRates.length === 0">Прошлых тарифов нет</GuiMessage>
        <TicketRate v-else-if="archive" v-for="rate in archivedRates" :rate="rate" :with-site-price="true">
            <GuiActionsMenu :title="null" v-if="editable">
                <span class="link" @click="edit(rate)">Редактировать</span>
                <span class="link" @click="createFrom(rate)">Копировать тариф</span>
                <span class="link" @click="remove(rate)">Удалить</span>
            </GuiActionsMenu>
        </TicketRate>

        <ExcursionRateForm v-if="data.is_loaded"
                           :excursionId="excursionId"
                           :providerId="providerId"
                           ref="rate_form"/>

        <PartnerTicketRateOverrideMass @update="update" ref="override"/>

    </LoadingProgress>
</template>

<script>
import data from "@/Core/Data";
import DeleteEntry from "@/Mixins/DeleteEntry";
import LoadingProgress from "@/Components/LoadingProgress";
import GuiHint from "@/Components/GUI/GuiHint";
import GuiButton from "@/Components/GUI/GuiButton";
import GuiHeading from "@/Components/GUI/GuiHeading";
import GuiMessage from "@/Components/GUI/GuiMessage";
import TicketRate from "@/Pages/Parts/Rates/TicketRate";
import GuiActionsMenu from "@/Components/GUI/GuiActionsMenu";
import GuiExpand from "@/Components/GUI/GuiExpand";
import ExcursionRateForm from "@/Pages/Admin/Excursions/Parts/ExcursionRateForm";
import PartnerTicketRateOverrideMass from "@/Pages/Admin/Partners/Parts/PartnerTicketRateOverrideMass.vue";

export default {
    components: {
        PartnerTicketRateOverrideMass,
        ExcursionRateForm,
        GuiExpand,
        GuiActionsMenu,
        TicketRate,
        GuiMessage,
        GuiHeading,
        GuiButton,
        GuiHint,
        LoadingProgress,
    },

    props: {
        excursionId: {type: Number, required: true},
        editable: {type: Boolean, default: false},
        providerId:  {type: Number, required: true},
    },

    mixins: [DeleteEntry],

    data: () => ({
        data: data('/api/rates'),
        archive: false,
    }),

    computed: {
        currentRate() {
            let current = null;
            if (Object.keys(this.data.data).length > 0) {
                this.data.data.some(rate => rate['current'] && (current = rate));
            }
            return current;
        },

        comingRates() {
            if (Object.keys(this.data.data).length === 0) {
                return [];
            }
            return this.data.data.filter(rate => !rate['current'] && !rate['archive']);
        },

        archivedRates() {
            if (Object.keys(this.data.data).length === 0) {
                return [];
            }
            return this.data.data.filter(rate => rate['archive']).reverse();
        },
    },

    created() {
        this.data.load({excursion_id: this.excursionId, archive: true});
    },

    methods: {
        create() {
            this.$refs.rate_form.handle(0, null)
                .then(response => {
                    this.update(response.payload.rate);
                });
        },
        edit(rate) {
            this.$refs.rate_form.handle(rate['id'], rate)
                .then(response => {
                    this.update(response.payload.rate);
                });
        },
        createFrom(rate) {
            this.$refs.rate_form.handle(0, rate)
                .then(response => {
                    this.update(response.payload.rate);
                });
        },
        update(payload) {
            let is_replaced = this.data.data.some((rate, key) => {
                if (rate['id'] === payload['id']) {
                    this.data.data[key] = payload;
                    return true;
                }
                return false;
            });
            if (!is_replaced) {
                this.data.data.push(payload);
            }
        },
        override(rate) {
            this.$refs.override.show(rate);
        },
        remove(rate) {
            let message;
            if (!rate['archive']) {
                message = 'Удаление тарифа приведет к остановке продаж билетов на рейсы в диапазоне дат ' + rate['start_at'] + ' - ' + rate['end_at'] + '. Продолжить?';
            } else {
                message = 'Удалить архивный тариф?';
            }
            const id = rate['id'];

            this.deleteEntry(message, '/api/rates/delete', {id: id})
                .then(() => {
                    this.data.data = this.data.data.filter(rate => rate['id'] !== id);
                });
        },
    }
}
</script>

