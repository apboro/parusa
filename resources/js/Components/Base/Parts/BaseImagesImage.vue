<template>
    <div class="base-files__image">
        <span class="base-files__image-discard" @click="$emit('discard', index)"></span>
        <img class="base-files__image-img" :src="!!image['content'] ? image['content'] : image['url']" alt="image">
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
