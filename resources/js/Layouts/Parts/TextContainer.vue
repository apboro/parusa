<template>
    <div class="text-container" :class="{
        'text-container__no-top': noTop,
        'text-container__no-bottom': noBottom,
    }">
        <p class="text-container__title" v-if="title">{{ title }}</p>
        <div class="text-container__wrapper">
            <slot/>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        noTop: {type: Boolean, default: false},
        noBottom: {type: Boolean, default: false},
        title: {type: String, default: null},
    }
}
</script>
