<template>
    <table class="list-table-responsive">
        <slot name="header" v-if="$slots.header"/>
        <ListTableResponsiveHeader v-else :titles="titles" :has-action="hasAction"/>
        <tbody class="list-table-responsive__body">
        <slot/>
        </tbody>
    </table>
</template>

<script>
import ListTableResponsiveHeader from "@/Components/ListTable/ListTableResponsiveHeader";

export default {
    components: {ListTableResponsiveHeader},
    props: {
        titles: {type: [Array, Object], default: null},
        hasAction: {type: Boolean, default: false},
    },
}
</script>

<style lang="scss">
.list-table-responsive {
    border-collapse: collapse;
    min-width: 100%;
}

@media (max-width: 767px) {
    .list-table-responsive {
        margin-top: 30px;
        border-top: 1px solid #0B68C2;
    }
}
</style>
