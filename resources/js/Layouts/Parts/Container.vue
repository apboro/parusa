<template>
    <div class="page__container" :class="{
        'page__container-no-top': noTop,
        'page__container-no-bottom': noBottom,
    }">
        <slot/>
    </div>
</template>

<script>
export default {
    props: {
        noTop: {type: Boolean, default: false},
        noBottom: {type: Boolean, default: false},
    }
}
</script>
