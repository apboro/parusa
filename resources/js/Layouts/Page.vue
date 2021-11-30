<template>
    <div class="page">
        <div class="page__header" v-if="$slots.header">
            <slot name="header"/>
        </div>
        <div class="page__body">
            <slot/>
        </div>
        <div class="page__footer" v-if="$slots.footer">
            <slot name="footer"/>
        </div>
    </div>
</template>

<script>
export default {
    name: "Page"
}
</script>

