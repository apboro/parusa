<template>
    <span class="base-table__cell-item" :class="{'base-table__cell-item-no-wrap': !wrap}">
        <slot/>
    </span>
</template>

<script>
export default {
    props: {
        wrap: {type: Boolean, default: true},
    }
}
</script>

<style lang="scss">
.base-table__cell-item {
    display: block;

    &-no-wrap {
        white-space: nowrap;
    }

    &:not(:last-child) {
        margin-bottom: 8px;
    }
}
</style>
