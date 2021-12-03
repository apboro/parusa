<template>
    <table class="base-table" :class="tableClass">
        <slot name="header"/>
        <tbody class="base-table__body">
        <slot/>
        </tbody>
    </table>
</template>

<script>
export default {
    props: {
        small: {type: Boolean, default: false},
        hover: {type: Boolean, default: true},
        highlight: {type: Boolean, default: true},
        borders: {type: Boolean, default: true},
    },

    computed: {
        tableClass() {
            return {
                'base-table__hover': this.hover,
                'base-table__highlight': this.highlight,
                'base-table__borders': this.borders,
                'base-table__small': this.small,
            }
        }
    }
}
</script>
