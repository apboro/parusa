<script>
import InputCheckbox from "@/Components/Inputs/InputCheckbox.vue";
import GuiValueArea from "@/Components/GUI/GuiValueArea.vue";
import form from "@/Core/Form";
import LoadingProgress from "@/Components/LoadingProgress.vue";
import GuiContainer from "@/Components/GUI/GuiContainer.vue";
import GuiButton from "@/Components/GUI/GuiButton.vue";

export default {
    name: "YagaSettings",
    components: {GuiButton, GuiContainer, LoadingProgress, GuiValueArea, InputCheckbox},
    data: () => ({
        form: form('/api/settings/yaga/get', '/api/settings/yaga/set'),
        ten_excursion_ids: [],
        fifteen_excursion_ids: [],
    }),
    computed: {
        excursions() {
            return this.form.payload['excursions'];
        },
        processing() {
            return this.form.is_loading || this.form.is_saving;
        },
    },
    watch:{
        'form.is_loaded'(){
            if (this.form.values) {
                this.ten_excursion_ids = this.form.values['ten_excursion_ids'] ? this.form.values['ten_excursion_ids'].split(',').map(Number) : [];
                this.fifteen_excursion_ids = this.form.values['fifteen_excursion_ids'] ? this.form.values['fifteen_excursion_ids'].split(',').map(Number) : [];
            }
        }
    },
    created() {
        this.form.toaster = this.$toast;
        this.form.load();
    },

    methods: {
        save() {
            this.form.set('ten_excursion_ids', this.ten_excursion_ids.toString())
            this.form.set('fifteen_excursion_ids', this.fifteen_excursion_ids.toString())

            if (!this.form.validate()) {
                return;
            }
            this.form.save();
        },
    }

}
</script>

<template>
    <LoadingProgress :loading="processing" style="display: flex">
        <GuiValueArea mt-30 :title="'Экскурсии 10%'">
            <template v-for="excursion in excursions">
                <InputCheckbox v-model="ten_excursion_ids" :value="excursion.id" :label="excursion.name"/>
            </template>
        </GuiValueArea>
        <GuiValueArea mt-30 :title="'Экскурсии 15%'">
            <template v-for="excursion in excursions">
                <InputCheckbox v-model="fifteen_excursion_ids" :value="excursion.id" :label="excursion.name"/>
            </template>
        </GuiValueArea>
    </LoadingProgress>
    <GuiContainer mt-30>
        <GuiButton @click="save" :color="'green'">Сохранить</GuiButton>
    </GuiContainer>

</template>

<style scoped lang="scss">

</style>
