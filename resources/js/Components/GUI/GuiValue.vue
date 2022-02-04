<template>
    <div class="value" :class="classProxy">
        <span class="value__title" :class="classFromExceptedAttributes">{{ title }}</span>
        <div class="value__value">
            <slot/>
        </div>
    </div>
</template>

<script>
import AttributeKeysToClass from "@/Components/Helpers/AttributeKeysToClass";

export default {
    inheritAttrs: false,
    props: {
        title: {type: String, default: null},
        dots: {type: Boolean, default: true},
    },
    mixins: [AttributeKeysToClass],

    data: () => ({
        attributesClassExcept: ['class'],
    }),

    computed: {
        classProxy() {
            if (this.dots) {
                let cls = this.classFromAttributes;
                cls.push('value__dotted')
                return cls;
            }
            return this.classFromAttributes;
        }
    }
}
</script>

<style lang="scss">
@import "../variables";

$project_font: -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica Neue, Arial, Noto Sans, sans-serif, Apple Color Emoji, Segoe UI Emoji, Segoe UI Symbol, Noto Color Emoji !default;
$base_black_color: #1e1e1e !default;
$base_text_gray_color: #3f3f3f !default;
$base_light_gray_color: #e5e5e5 !default;

.value {
    width: 100%;
    display: flex;
    flex-direction: row;
    padding: 10px 0;
    font-family: $project_font;

    &__dotted {
        border-bottom: 1px dashed $base_light_gray_color;
    }

    &__title {
        width: 200px;
        flex-shrink: 0;
        font-size: 14px;
        color: $base_text_gray_color;
        display: flex;
        align-items: center;
    }

    &__value {
        flex-grow: 1;
        font-size: 14px;
        color: $base_black_color;
    }
}
</style>
