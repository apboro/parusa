<template>
    <div class="page__body-bar-item">
        <div class="page__body-bar-item-title" :class="{'page__body-bar-item-title-empty': !title}">
            <span v-if="title">{{ title }}</span>
        </div>
        <div class="page__body-bar-item-container">
            <slot/>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        title: {
            type: String,
            default: null,
        }
    }
}
</script>
