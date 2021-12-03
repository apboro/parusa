<template>
    <table class="base-table" :class="{
        'base-table__hover': hover,
        'base-table__highlight': highlight,
        'base-table__borders': borders,
        'base-table__small': small,
    }">
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
    }
}
</script>
