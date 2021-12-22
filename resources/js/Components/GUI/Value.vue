<template>
    <div class="value" :class="classProxy">
        <span class="value__title" :class="classFromExceptedAttributes">{{ title }}</span>
        <div class="value__value">
            <slot/>
        </div>
    </div>
</template>

<script>
import AttributeKeysToClass from "../../Mixins/AttributeKeysToClass";
import clone from "../../Helpers/Lib/clone";

export default {
    props: {
        title: {type: String, default: null},
        dots: {type: Boolean, default: true},
    },
    inheritAttrs: false,
    mixins: [AttributeKeysToClass],

    data: () => ({
        attributesClassExcept: ['class'],
    }),

    computed: {
        classProxy() {
            if (this.dots) {
                let cls = clone(this.classFromAttributes);
                cls.push('value__dotted')
                return cls;
            }
            return this.classFromAttributes;
        }
    }
}
</script>
