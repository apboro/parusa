<template>
    <td class="base-table__cell" :class="{'base-table__cell-nonbreakable': nowrap}">
        <slot/>
    </td>
</template>

<script>
export default {
    props: {
        nowrap: {
            type: Boolean,
            default: false,
        }
    }
}
</script>
