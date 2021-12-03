<template>
    <router-link :to="to" class="button" :class="classProxy">
        <slot></slot>
    </router-link>
</template>

<script>
export default {
    props: {
        identifier: {type: String, default: null},
        customClass: {type: String | Array | Object, default: null},
        to: {type: Object, default: null},
    },

    computed: {
        classProxy() {
            return this.customClass;
        }
    },
}
</script>
