<template>
    <div class="loading-progress__container">
        <div v-if="loading" class="loading-progress__wrapper">
            <div class="loading-progress"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>
        </div>
        <slot></slot>
    </div>
</template>

<script>
export default {
    props: {
        loading: {
            type: Boolean,
            default: false,
        }
    }
}
</script>

<style scoped>

</style>
