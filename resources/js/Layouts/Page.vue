<template>
    <div class="page">
        <loading-progress :loading="loading" :opacity="0">
            <div class="page__header" v-if="$slots.header">
                <slot name="header"/>
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
import LoadingProgress from "../Components/LoadingProgress";

export default {
    components: {
        LoadingProgress,
    },

    props: {
        loading: {
            type: Boolean,
            default: false,
        }
    }
}
</script>

