<template>
    <div>
        <GuiValueArea mt-30 :title="'Выберите экскурсии:'">
            <GuiButton v-if="data.data['excursions']" @clicked="checkAll"> {{excursions.length === data.data['excursions'].length ? 'Очистить выбор' : 'Выбрать все' }} </GuiButton>
            <template v-for="excursion in data.data['excursions']">
                <InputCheckbox v-model="excursions" :value="excursion.id" :label="excursion.name"
                               @change="updateScript"/>
            </template>
        </GuiValueArea>

        <LayoutRoutedTabs :tabs="{
            showcase1: 'Код для вставки витрины на сайте Вариант №1',
            showcase2: 'Код для вставки витрины на сайте Вариант №2',
            showcase3: 'Код для вставки витрины на сайте Вариант №3'
        }" @change="tab = $event"/>

        <LoadingProgress :loading="data.is_loading">
            <GuiValueArea v-if="tab === 'showcase1'" mt-5 :text-content="data.data['code']"/>
            <GuiValueArea v-if="tab === 'showcase2'" mt-5 :text-content="data.data['code2']"/>
            <GuiValueArea v-if="tab === 'showcase3'" mt-5 :text-content="data.data['code3']"/>
            <div>
                <GuiHeading v-if="tab === 'showcase1'">Внешний вид витрины (вариант № 1)</GuiHeading>
                <GuiHeading v-if="tab === 'showcase2'">Внешний вид витрины (вариант № 2)</GuiHeading>
                <GuiHeading v-if="tab === 'showcase3'">Внешний вид витрины (вариант № 3)</GuiHeading>
                <img class="vitrina-image" alt="Витрина №1" v-if="tab === 'showcase1'"
                     src="/storage/images/vitrina1.png">
                <img class="vitrina-image" alt="Витрина №2" v-if="tab === 'showcase2'"
                     src="/storage/images/vitrina2.png">
                <img class="vitrina-image" alt="Витрина №3" v-if="tab === 'showcase3'"
                     src="/storage/images/vitrina3.png">
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
        },
        checkAll(){
            if (this.excursions.length === this.data.data['excursions'].length) {
                this.excursions = [];
            } else {
                this.excursions = this.data.data['excursions'].map((excursion) => excursion.id)
            }
            this.updateScript()
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
