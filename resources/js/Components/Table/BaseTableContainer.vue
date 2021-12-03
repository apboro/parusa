<template>
    <div class="base-table__container" :class="containerClass">
        <slot/>
    </div>
</template>

<script>
export default {
    props: {
        w: {type: String, default: null},
    },
    computed: {
        containerClass() {
            return this.w ? 'base-table__container-w-' + this.w : null;
        },
    }
}
</script>
