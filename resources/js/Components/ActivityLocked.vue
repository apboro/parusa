<template>
    <icon-lock :class="['activity-locked', 'activity-locked__locked']" v-if="locked"/>
    <icon-unlock :class="['activity-locked', 'activity-locked__unlocked']" v-else/>
</template>

<script>
import IconLock from "./Icons/IconLock";
import IconUnlock from "./Icons/IconUnlock";

export default {
    components: {IconUnlock, IconLock},
    props: {
        locked: {
            type: Boolean,
            default: true
        }
    },
}
</script>
