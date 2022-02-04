<template>
    <IconBan class="activity-locked activity-locked__locked" v-if="locked"/>
    <IconArrowRight class="activity-locked activity-locked__unlocked" v-else/>
</template>

<script>
import IconArrowRight from "../Icons/IconArrowRight";
import IconBan from "../Icons/IconBan";

export default {
    components: {
        IconBan,
        IconArrowRight,
    },
    props: {
        locked: {type: Boolean, default: true}
    },
}
</script>

<style lang="scss">
@import "../variables";

$base_disabled_color: #727272 !default;
$base_active_color: #00c500 !default;
$base_inactive_color: #db0000 !default;

.activity-locked {
    display: inline-block;
    width: 10px;
    height: 10px;
    vertical-align: baseline;
    margin-right: 6px;
    top: -1px;
    color: $base_disabled_color;

    &__unlocked {
        color: $base_active_color;
    }

    &__locked {
        color: $base_inactive_color;
    }
}
</style>
