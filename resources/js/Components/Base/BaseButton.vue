<template>
    <button class="button" :class="classProxy" @click.stop.prevent="$emit('clicked', identifier)">
        <slot></slot>
    </button>
</template>

<script>
export default {
    props: {
        identifier: {type: String, default: null},
        customClass: {type: String | Array | Object, default: null},
    },

    computed: {
        classProxy() {
            return this.customClass;
        }
    },
}
</script>
