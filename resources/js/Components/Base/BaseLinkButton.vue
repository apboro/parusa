<template>
    <router-link :to="to" class="button" :class="classProxy">
        <slot></slot>
    </router-link>
</template>

<script>
export default {
    props: {
        identifier: {type: String, default: null},
        type: {type: String, default: null},
        to: {type: Object, default: null},
    },

    computed: {
        classProxy() {
            return 'button__' + this.type;
        }
    },
}
</script>
