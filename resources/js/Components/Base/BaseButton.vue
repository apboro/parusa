<template>
    <button class="button" :class="classProxy" @click.stop.prevent="$emit('clicked', identifier)">
        <slot></slot>
    </button>
</template>

<script>
export default {
    props: {
        identifier: {type: String, default: null},
        color: {type: String, default: null},
        disabled: {type: Boolean, default: false},
    },

    computed: {
        classProxy() {
            return 'button__' + this.color + (this.disabled ? ' button__disabled' : '');
        }
    },
}
</script>
