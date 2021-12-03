<template>
    <div class="base-table__container" :class="[w ? 'base-table__container-w-'+w : '']">
        <slot/>
    </div>
</template>

<script>
export default {
    props: {
        w: {type: String, default: null},
    },
}
</script>
