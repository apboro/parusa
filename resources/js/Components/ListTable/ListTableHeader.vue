<template>
    <thead class="list-table__header" v-if="titles">
    <tr class="list-table__header-row">
        <th class="list-table__header-cell" v-for="title in titlesProxy">{{ title }}</th>
        <th class="list-table__header-cell base-table__header-cell-actions" v-if="hasAction"></th>
    </tr>
    </thead>
</template>

<script>

export default {
    props: {
        titles: {type: [Array, Object], default: null},
        hasAction: {type: Boolean, default: false},
    },

    computed: {
        titlesProxy() {
            return (this.titles !== null && Object.keys(this.titles).length > 0) ? this.titles : null;
        }
    }
}
</script>

<style lang="scss">
@import "../variables";

$project_font: -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica Neue, Arial, Noto Sans, sans-serif, Apple Color Emoji, Segoe UI Emoji, Segoe UI Symbol, Noto Color Emoji !default;
$list_table_header_color: #1e1e1e !default;
$list_table_header_background_color: #e3ecf7 !default;

.list-table__header {
    background-color: $list_table_header_background_color;

    &-row {
    }

    &-cell {
        color: $list_table_header_color;
        vertical-align: middle;
        font-family: $project_font;
        font-size: 14px;
        text-transform: uppercase;
        box-sizing: border-box;
        padding: 15px 15px;
        min-height: 60px;
        font-weight: normal;
        text-align: left;

        &-actions {
            width: 70px;
        }
    }
}
</style>
