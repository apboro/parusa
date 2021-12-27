<template>
    <icon-ban :class="['activity-locked', 'activity-locked__locked']" v-if="locked"/>
    <icon-arrow-right :class="['activity-locked', 'activity-locked__unlocked']" v-else/>
</template>

<script>
import IconArrowRight from "./Icons/IconArrowRight";
import IconBan from "./Icons/IconBan";

export default {
    components: {
        IconBan,
        IconArrowRight,
    },
    props: {
        locked: {
            type: Boolean,
            default: true
        }
    },
}
</script>
