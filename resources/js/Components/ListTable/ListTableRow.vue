<template>
    <tr class="list-table__row" :class="{'list-table__row-no-highlight': noHighlight, 'list-table__row-no-even-odd': noOddEven}">
        <slot/>
    </tr>
</template>

<script>
export default {
    props: {
        noHighlight: {type: Boolean, default: false},
        noOddEven: {type: Boolean, default: false},
    }
}
</script>

<style lang="scss">
@import "../variables";

$list_table_border_color: #e3e3e3 !default;
$list_table_hover_background_color: #eaf5ff !default;
$list_table_odd_background_color: #ffffff !default;
$list_table_even_background_color: #f9f8f8 !default;

.list-table__row {
    border-bottom: 1px solid $list_table_border_color;

    &:not(&-no-even-odd):nth-child(odd) {
        background-color: $list_table_odd_background_color;
    }

    &:not(&-no-even-odd):nth-child(even) {
        background-color: $list_table_even_background_color;
    }

    &:not(&-no-highlight):hover {
        background-color: $list_table_hover_background_color;
    }
}
</style>
