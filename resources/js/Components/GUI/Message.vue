<template>
    <div class="message" :class="classFromAttributes">
        <slot/>
    </div>
</template>

<script>

import AttributeKeysToClass from "@/Mixins/AttributeKeysToClass";

export default {
    mixins: [AttributeKeysToClass],
}
</script>
