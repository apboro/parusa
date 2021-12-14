<template>
    <div class="base-images__image">
        <span class="base-images__image-discard" @click="$emit('discard', index)"></span>
        <img class="base-images__image-src" :src="!!image['content'] ? image['content'] : image['url']" alt="image">
    </div>
</template>

<script>
export default {
    props: {
        index: Number,
        image: Object,
    },

    emits: ['discard'],
}
</script>
