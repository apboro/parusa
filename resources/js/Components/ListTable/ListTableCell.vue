<template>
    <td class="list-table__cell" :class="cellClass">
        <slot/>
    </td>
</template>

<script>
export default {
    props: {
        nowrap: {
            type: Boolean,
            default: false,
        },
    },

    computed: {
        cellClass() {
            return [
                this.nowrap ? 'list-table__cell-nowrap' : '',
            ]
        },
    },
}
</script>

<style lang="scss">
@import "../variables";

$project_font: -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica Neue, Arial, Noto Sans, sans-serif, Apple Color Emoji, Segoe UI Emoji, Segoe UI Symbol, Noto Color Emoji !default;
$list_table_cell_color: #1e1e1e;

.list-table__cell {
    box-sizing: border-box;
    padding: 13px 15px;
    min-height: 60px;
    vertical-align: top;
    font-family: $project_font;
    font-size: 14px;
    color: $list_table_cell_color;

    &-nowrap {
        white-space: nowrap;
    }

    & > div {
        display: block;

        &:not(:last-child) {
            margin-bottom: 8px;
        }
    }
}
</style>
