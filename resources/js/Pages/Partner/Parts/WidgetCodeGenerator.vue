<template>
    <div>
        <GuiValueArea mt-30 :title="'Выберите экскурсии:'">
            <template v-for="excursion in data.data['excursions']">
                <InputCheckbox v-model="excursions" :value="excursion.id" :label="excursion.name" @change="updateScript"/>
            </template>
        </GuiValueArea>

        <LayoutRoutedTabs :tabs="{showcase1: 'Код для вставки витрины на сайте Вариант №1', showcase2: 'Код для вставки витрины на сайте Вариант №2'}" @change="tab = $event"/>

        <LoadingProgress :loading="data.is_loading">
            <GuiValueArea v-if = "tab === 'showcase1'" mt-5 :text-content="data.data['code']"/>
            <GuiValueArea v-if = "tab === 'showcase2'" mt-5 :text-content="data.data['code2']"/>
            <div>
                <GuiHeading v-if="tab === 'showcase1'">Внешний вид витрины (вариант № 1)</GuiHeading>
                <GuiHeading v-if="tab === 'showcase2'">Внешний вид витрины (вариант № 2)</GuiHeading>
                <img class="vitrina-image" alt="Витрина №1" v-if="tab === 'showcase1'" src="/storage/images/vitrina1.png">
                <img class="vitrina-image" alt="Витрина №2" v-if="tab === 'showcase2'" src="/storage/images/vitrina2.png">
            </div>
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
import LayoutRoutedTabs from "@/Components/Layout/LayoutRoutedTabs.vue";

export default {
    components: {
        LayoutRoutedTabs,
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
        tab: 'showcase1',
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
<style>
.vitrina-image {
    max-width: 100%;
    height: auto;
}
</style>
