<template>
    <div class="page">
        <div class="page__header" v-if="$slots.header">
            <slot name="header"/>
        </div>
        <div class="page__body">
            <loading-progress :loading="loading">
                <slot/>
            </loading-progress>
        </div>
        <div class="page__footer" v-if="$slots.footer">
            <slot name="footer"/>
        </div>
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

