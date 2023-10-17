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
        },
    }
}
</script>

<template>
    <GuiValueArea mt-30 :title="'Инвентарь'">
        <template v-for="item in inventory">
            <InputCheckbox v-model="promoterInventory" :value="item.id" :label="item.name"/>
        </template>
    </GuiValueArea>
    <GuiContainer mt-30>
        <GuiButton @click="save" :color="'green'">Сохранить</GuiButton>
    </GuiContainer>
</template>

<style scoped lang="scss">

</style>
