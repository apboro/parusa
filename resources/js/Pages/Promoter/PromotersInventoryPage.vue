<script>
import LayoutPage from "@/Components/Layout/LayoutPage.vue";
import GuiValueArea from "@/Components/GUI/GuiValueArea.vue";
import GuiValue from "@/Components/GUI/GuiValue.vue";

export default {
    name: "PromotersInventoryPage",
    components: {GuiValue, GuiValueArea, LayoutPage},
    data: () => ({
        inventory: null,
    }),

    mounted() {
        axios.post('/api/promoters/inventory/get')
            .then(response => this.inventory = response.data.data.inventory)
    },
}
</script>

<template>
    <LayoutPage :title="$route.meta['title']">
        <GuiValueArea>
            <li v-for="item in inventory">
                {{item.dictionary.name}}
            </li>
        </GuiValueArea>
    </LayoutPage>
</template>

<style scoped lang="scss">

</style>
