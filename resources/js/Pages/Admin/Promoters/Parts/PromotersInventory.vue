<script>
import InputCheckbox from "@/Components/Inputs/InputCheckbox.vue";
import GuiValueArea from "@/Components/GUI/GuiValueArea.vue";
import GuiContainer from "@/Components/GUI/GuiContainer.vue";
import GuiButton from "@/Components/GUI/GuiButton.vue";

export default {
    name: "PromotersInventory",
    components: {GuiContainer, GuiButton, GuiValueArea, InputCheckbox},
    props: {
        promoterId: Number
    },

    data: () => ({
        inventory: null,
        promoterInventory: null,
        editing: false,
    }),

    mounted() {
        axios('/api/promoters/' + this.promoterId + '/inventory')
            .then((response) => {
                this.inventory = response.data.data.inventory;
                this.promoterInventory = response.data.data.promoterInventory;
            })
            .catch(error => {
                this.inventory = null;
                this.promoterInventory = null;
                this.$toast.error(error.response.data.message, 5000);
            })
    },

    methods: {
        save() {
            axios.post('/api/promoters/inventory', {
                promoterId: this.promoterId,
                promoterInventory: this.promoterInventory
            })
                .then(response => this.$toast.success(response.data.message, 3000))
                .finally(this.editing = false)
        },
    }
}
</script>

<template>
    <GuiValueArea mt-30 :title="'Инвентарь'">
        <template v-for="item in inventory">
            <InputCheckbox v-model="promoterInventory" :value="item.id" :label="item.name" :disabled="!editing"/>
        </template>
    </GuiValueArea>
    <GuiContainer mt-30>
        <GuiButton v-if="editing" @click="save" :color="'green'">Сохранить</GuiButton>
        <GuiButton v-if="!editing" @click="editing = true" :color="'blue'">Редактировать</GuiButton>
    </GuiContainer>
</template>

<style scoped lang="scss">

</style>
