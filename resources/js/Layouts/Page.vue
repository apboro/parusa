<template>
    <div class="page">
        <loading-progress :loading="loading">
            <div class="page__header" v-if="$slots.header">
                <slot name="header"/>
            </div>
            <div class="page__header" v-if="title">
                <page-title-bar :title="title"/>
            </div>
            <div class="page__body">
                <slot/>
            </div>
            <div class="page__footer" v-if="$slots.footer">
                <slot name="footer"/>
            </div>
        </loading-progress>
    </div>
</template>

<script>
import LoadingProgress from "@/Components/LoadingProgress";
import PageTitleBar from "@/Layouts/Parts/PageTitleBar";

export default {
    components: {
        PageTitleBar,
        LoadingProgress,
    },

    props: {
        loading: {type: Boolean, default: false},
        title: {type: String, default: null},
    },
}
</script>

