<template>
    <div class="loading-progress__container">
        <div v-if="loading" class="loading-progress__wrapper" :class="'loading-progress__wrapper-' + opacity">
            <div class="loading-progress"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>
        </div>
        <slot></slot>
    </div>
</template>

<script>
export default {
    props: {
        loading: {type: Boolean, default: false},
        opacity: {type: Number, default: 30}
    }
}
</script>
