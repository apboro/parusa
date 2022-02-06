<template>
    <tr class="list-table__row">
        <slot/>
    </tr>
</template>

<script>
export default {}
</script>

<style lang="scss">
@import "../variables";

$list_table_border_color: #e3e3e3 !default;
$list_table_hover_background_color: #f5faff !default;
$list_table_odd_background_color: #ffffff !default;
$list_table_even_background_color: #fbfbfb !default;

.list-table__row {
    border-bottom: 1px solid $list_table_border_color;

    &:nth-child(odd) {
        background-color: $list_table_odd_background_color;
    }

    &:nth-child(even) {
        background-color: $list_table_even_background_color;
    }

    &:hover {
        background-color: $list_table_hover_background_color;
    }
}
</style>
