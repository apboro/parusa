<template>
    <div class="heading" :class="classFromAttributes">
        <slot/>
    </div>
</template>

<script>
import AttributeKeysToClass from "../../Mixins/AttributeKeysToClass";

export default {
    inheritAttrs: false,
    mixins: [AttributeKeysToClass],
}
</script>
