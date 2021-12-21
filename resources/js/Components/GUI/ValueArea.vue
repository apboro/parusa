<template>
    <div class="value-area" :class="classFromAttributes">
        <span class="value-area__title" :class="classFromExceptedAttributes">{{ title }}</span>
        <div class="value-area__value">
            <slot v-if="$slots.default"/>
            <template v-if="text">
                <span class="value-area__value-paragraph" v-for="paragraph in text">{{ paragraph }}</span>
            </template>
        </div>
    </div>
</template>

<script>
import AttributeKeysToClass from "../../Mixins/AttributeKeysToClass";

export default {
    props: {
        title: {type: String, default: null},
        textContent: {type: String, default: null},
    },
    inheritAttrs: false,
    mixins: [AttributeKeysToClass],
    computed: {
        text() {
            if (this.textContent === null) {
                return null;
            }

            return String(this.textContent).split("\n");
        },
    },
    data: () => ({
        attributesClassExcept: ['class'],
    }),
}
</script>
