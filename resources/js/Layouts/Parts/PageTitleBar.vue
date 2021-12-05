<template>
    <div class="page-title-bar">
        <div class="page-title-bar__head">
            <div class="page-title-bar__head-breadcrumbs" v-if="breadcrumbs">
                <template v-for="link in breadcrumbs">
                    <router-link class="link link__bold" :to="link['to']">{{ link['caption'] }}</router-link>
                    <span class="page-title-bar__head-breadcrumbs-divider">{{ divider }}</span>
                </template>
            </div>
            <span class="page-title-bar__head-title">{{ title }}</span>
        </div>
        <div class="page-title-bar__actions" v-if="$slots.default">
            <slot/>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        title: String,
        breadcrumbs: {type: Array, default: null},
        divider: {type: String, default: '/'},
    }
}
</script>
