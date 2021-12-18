<template>
    <div class="value" :class="classFromAttributes">
        <span class="value__title" :class="classFromExceptedAttributes">{{ title }}</span>
        <div class="value__value">
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
