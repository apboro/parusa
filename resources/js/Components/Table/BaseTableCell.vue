<template>
    <td class="base-table__cell" :class="[
        nowrap ? 'base-table__cell-nonbreakable' : '',
        bold ? 'base-table__cell-bold' : '',
        w ? 'base-table__cell-w-' + w : '',
        text ? 'base-table__cell-text-' + text : '',
    ]">
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
            default: false,
        },
        bold: {
            type: Boolean,
            default: false,
        },
        text: {
            type: String,
            default: false,
        },
    }
}
</script>
