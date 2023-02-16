<template>
    <td class="list-table-responsive__cell" :class="cellClass">
        <b class="list-table-responsive__cell-title" v-if="mobileTitle">{{ mobileTitle }}: </b>
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
        mobileTitle: {
            type: String,
            default: null,
        },
    },

    computed: {
        cellClass() {
            return [
                this.nowrap ? 'list-table-responsive__cell-nowrap' : '',
            ]
        },
    },
}
</script>

<style lang="scss">
@import "../variables";

$project_font: -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica Neue, Arial, Noto Sans, sans-serif, Apple Color Emoji, Segoe UI Emoji, Segoe UI Symbol, Noto Color Emoji !default;
$list_table_cell_color: #1e1e1e;

.list-table-responsive__cell {
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

.list-table-responsive__cell-title {
    display: none;
}

@media (max-width: 767px) {
    .list-table-responsive__cell-title {
        display: block;
        margin-bottom: 5px;
    }

    .list-table-responsive__cell {
        padding: 13px 0 !important;
    }
}
</style>
