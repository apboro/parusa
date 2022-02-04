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
import AttributeKeysToClass from "@/Components/Helpers/AttributeKeysToClass";

export default {
    inheritAttrs: false,
    props: {
        title: {type: String, default: null},
        textContent: {type: String, default: null},
    },
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

<style lang="scss">
@import "../variables";

$project_font: -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica Neue, Arial, Noto Sans, sans-serif, Apple Color Emoji, Segoe UI Emoji, Segoe UI Symbol, Noto Color Emoji !default;
$base_black_color: #1e1e1e !default;
$base_text_gray_color: #3f3f3f !default;
$base_light_gray_color: #e5e5e5 !default;

.value-area {
    width: 100%;
    display: flex;
    flex-direction: column;
    padding: 10px 0;
    font-family: $project_font;

    &__title {
        font-size: 14px;
        margin-bottom: 10px;
        color: $base_text_gray_color;
    }

    &__value {
        font-size: 14px;
        box-sizing: border-box;
        padding: 15px;
        border: 1px solid $base_light_gray_color;
        border-radius: 2px;
        line-height: 20px;
        letter-spacing: 0.02em;
        color: $base_black_color;

        &-paragraph {
            display: block;

            &:not(:last-child) {
                margin-bottom: 5px;
            }
        }
    }
}
</style>
