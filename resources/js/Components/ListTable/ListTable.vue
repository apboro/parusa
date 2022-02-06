<template>
    <table class="list-table">
        <slot name="header" v-if="$slots.header"/>
        <ListTableHeader v-else :titles="titles" :has-action="hasAction"/>
        <tbody class="list-table__body">
        <slot/>
        </tbody>
    </table>
</template>

<script>
import ListTableHeader from "@/Components/ListTable/ListTableHeader";

export default {
    components: {ListTableHeader},
    props: {
        titles: {type: [Array, Object], default: null},
        hasAction: {type: Boolean, default: false},
    },
}
</script>

<style lang="scss">
.list-table {
    border-collapse: collapse;
    min-width: 100%;
}
</style>
