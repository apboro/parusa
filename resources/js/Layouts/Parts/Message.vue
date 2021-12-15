<template>
    <div class="page__message" :class="{'page__message-error': error}"><slot/></div>
</template>

<script>
export default {
    props: {
        error: {type: Boolean, default: false},
    }
}
</script>
