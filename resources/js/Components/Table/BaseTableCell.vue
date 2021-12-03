<template>
    <td class="base-table__cell" :class="cellClass">
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
        w: {
            type: String,
            default: null,
        },
        bold: {
            type: Boolean,
            default: false,
        },
        size: {
            type: String,
            default: null,
        },
    },

    computed: {
        cellClass() {
            return [
                this.nowrap ? 'base-table__cell-nonbreakable' : '',
                this.bold ? 'base-table__cell-bold' : '',
                this.w ? 'base-table__cell-w-' + this.w : '',
                this.size ? 'base-table__cell-text-' + this.size : ''
            ]
        },
    },
}
</script>
