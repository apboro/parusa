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
            return w ? 'base-table__container-w-' + w : null;
        },
    }
}
</script>
