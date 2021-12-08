<template>
    <div class="page">
        <loading-progress :loading="loading">
            <div class="page__header" v-if="$slots.header">
                <slot name="header"/>
            </div>
            <div class="page__body">
                <div class="page__body-bar" v-if="$slots.filters || $slots.search">
                    <div class="page__body-bar-filters" v-if="$slots.filters">
                        <slot name="filters"/>
                    </div>
                    <div class="page__body-bar-search" v-if="$slots.search">
                        <slot name="search"/>
                    </div>
                </div>
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

