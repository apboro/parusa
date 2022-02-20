<template>
    <div class="input-images__image">
        <span class="input-images__image-discard" @click="$emit('discard', index)"></span>
        <img class="input-images__image-img" :src="!!image['content'] ? image['content'] : image['url']" alt="image">
    </div>
</template>

<script>
export default {
    props: {
        index: Number,
        image: Object,
    },

    emits: ['discard'],
}
</script>

<style lang="scss">
@import "../../variables";

$base_size_unit: 35px !default;
$animation_time: 150ms !default;
$animation: cubic-bezier(0.24, 0.19, 0.28, 1.29) !default;
$input_background_color: #ffffff !default;
$input_remove_color: #FF1E00 !default;
$input_border_color: #b7b7b7 !default;

.input-images {
    &__image {
        display: inline-flex;
        position: relative;
        vertical-align: top;
        width: $base_size_unit * 4;
        height: $base_size_unit * 4;
        box-sizing: border-box;
        padding: 5px;
        margin: 5px;
        justify-content: center;
        align-items: center;
        background-color: $input_background_color;
        border-radius: 2px;
        border: 1px solid $input_border_color;

        &-discard {
            position: absolute;
            width: 20px;
            display: block;
            height: 20px;
            border-radius: 50%;
            top: 4px;
            right: 4px;
            cursor: pointer;
            background-color: $input_remove_color;
            opacity: 0.5;
            transition: opacity $animation $animation_time;

            &:hover {
                opacity: 1;
            }

            &:before, &:after {
                content: '';
                width: 65%;
                height: 2px;
                position: absolute;
                top: 50%;
                left: 50%;
                background-color: $input_background_color;
            }

            &:before {
                transform: translate(-50%, -50%) rotate(-45deg);
            }

            &:after {
                transform: translate(-50%, -50%) rotate(45deg);
            }
        }

        &-img {
            max-width: 100%;
            max-height: 100%;
        }
    }
}
</style>
