<template>
    <div>
        <GuiValueArea mt-30 :title="'Выберите экскурсии:'">
            <template v-for="excursion in data.data['excursions']">
                <InputCheckbox v-model="excursions" :value="excursion.id" :label="excursion.name" @change="updateScript"/>
            </template>
        </GuiValueArea>

        <LoadingProgress :loading="data.is_loading">
            <GuiValueArea mt-30 v-if="data.is_loaded" :title="'Код для вставки витрины на сайт'" :text-content="data.data['code']"/>
        </LoadingProgress>
    </div>
</template>

<script>
import GuiContainer from "@/Components/GUI/GuiContainer";
import GuiHeading from "@/Components/GUI/GuiHeading";
import GuiValueArea from "@/Components/GUI/GuiValueArea";
import InputString from "@/Components/Inputs/InputString";
import GuiButton from "@/Components/GUI/GuiButton";
import GuiText from "@/Components/GUI/GuiText";
import LoadingProgress from "@/Components/LoadingProgress";
import PopUp from "@/Components/PopUp";
import InputCheckbox from "@/Components/Inputs/InputCheckbox.vue";
import data from "@/Core/Data";

export default {
    components: {
        InputCheckbox,
        PopUp,
        LoadingProgress,
        GuiText,
        GuiButton,
        InputString,
        GuiValueArea,
        GuiHeading,
        GuiContainer,
    },

    data: () => ({
        data: data('/api/partners/partner/settings/widget'),
        excursions: [],
        scripts: null,
    }),

    created() {
        this.data.load();
    },

    methods: {
        updateData() {
            if (this.data && this.data['qr_target_page']) {
                this.qr_target_page = this.data['qr_target_page'];
            }
        },
        updateScript() {
            this.data.load({excursions: this.excursions});
        }
    }
}
</script>
