<template>
    <div class="page">
        <div class="page__header" v-if="$slots.header">
            <slot name="header"/>
        </div>
        <div class="page__body">
            <loading-progress :loading="loading">
                <div class="page__body-bar" v-if="$slots.filters || $slots.search">
                    <div class="page__body-bar-filters" v-if="$slots.filters">
                        <slot name="filters"/>
                    </div>
                    <div class="page__body-bar-search" v-if="$slots.search">
                        <slot name="search"/>
                    </div>
                </div>
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

