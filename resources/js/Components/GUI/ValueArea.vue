<template>
    <div class="value-area" :class="classFromAttributes">
        <span class="value-area__title" :class="classFromExceptedAttributes">{{ title }}</span>
        <div class="value-area__value">
            <slot/>
        </div>
    </div>
</template>

<script>
import AttributeKeysToClass from "../../Mixins/AttributeKeysToClass";

export default {
    props: {
        title: {type: String, default: null},
    },
    inheritAttrs: false,
    mixins: [AttributeKeysToClass],

    data: () => ({
        attributesClassExcept: ['class'],
    }),
}
</script>
