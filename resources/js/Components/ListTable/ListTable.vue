<template>
    <div class="list-table__wrapper">
        <table class="list-table">
            <slot name="header" v-if="$slots.header"/>
            <ListTableHeader v-else :titles="titles" :has-action="hasAction" :class="{'for-sticky': stickySecondCol}"/>
            <tbody class="list-table__body" :class="{'for-sticky': stickySecondCol }">
            <slot/>
            </tbody>
        </table>
    </div>
</template>

<script>
import ListTableHeader from "@/Components/ListTable/ListTableHeader";

export default {
    components: {ListTableHeader},
    props: {
        stickySecondCol: {type: Boolean, default: false},
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

@media (max-width: 767px) {
    .list-table__header-row th:nth-child(1) {
        position: sticky;
        left: 0;
        background: #e3ecf7;
        width: 26%;
    }

    .list-table__row > td:nth-child(1) {
        position: sticky;
        left: 0;
        background: #fff;
        width: 26%;
    }
    .for-sticky {
        .list-table__header-row > th:nth-child(2) {
            position: sticky;
            left: 24%;
            background: #e3ecf7;
        }

        .list-table__row > td:nth-child(2) {
            position: sticky;
            left: 24%;
            background: #fff;
        }
    }
}
</style>
