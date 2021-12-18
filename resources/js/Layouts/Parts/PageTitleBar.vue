<template>
    <div class="page-title-bar">
        <div class="page-title-bar__head">
            <div class="page-title-bar__head-breadcrumbs" v-if="breadcrumbs">
                <template v-for="link in breadcrumbs">
                    <router-link class="link link__bold" :to="link['to']">{{ link['caption'] }}</router-link>
                    <span class="page-title-bar__head-breadcrumbs-divider">{{ divider }}</span>
                </template>
            </div>
            <router-link v-if="titleLink" :to="titleLink" class="page-title-bar__head-title link">{{ title }}</router-link>
            <span v-else class="page-title-bar__head-title">{{ title }}</span>
        </div>
        <div class="page-title-bar__actions" v-if="$slots.default || link">
            <div class="page-title-bar__actions-link" v-if="link">
                <router-link :class="'link'" :to="link">{{ linkTitle }}</router-link>
            </div>
            <slot v-if="$slots.default"/>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        title: String,
        titleLink: {type: Object, default: null},
        breadcrumbs: {type: Array, default: null},
        divider: {type: String, default: '/'},
        link: {type: Object, default: null},
        linkTitle: {type: String, default: null},
    }
}
</script>
