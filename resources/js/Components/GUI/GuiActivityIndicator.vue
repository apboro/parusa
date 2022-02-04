<template>
    <span class="activity" :class="stateClass"></span>
</template>

<script>
export default {
    props: {
        active: {type: Boolean, default: null}
    },
    computed: {
        stateClass() {
            if (this.active === null) {
                return null;
            }

            return this.active ? 'activity__active' : 'activity__inactive';
        },
    }
}
</script>

<style lang="scss">
@import "../variables";

$base_disabled_color: #727272 !default;
$base_active_color: #00c500 !default;
$base_inactive_color: #db0000 !default;

.activity {
    display: inline-block;
    width: 7px;
    height: 7px;
    vertical-align: baseline;
    background-color: $base_disabled_color;
    border-radius: 50%;
    margin-right: 8px;
    position: relative;
    top: -1px;

    &__active {
        background-color: $base_active_color;
    }

    &__inactive {
        background-color: $base_inactive_color;
    }
}
</style>
