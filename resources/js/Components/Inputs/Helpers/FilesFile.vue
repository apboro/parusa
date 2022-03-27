<template>
    <div class="input-files__file">
        <span class="input-files__file-discard" @click="$emit('discard', index)"></span>
        <div class="input-files__file-icon">
            <icon-file-type :type="type"/>
        </div>
        <span class="input-files__file-name" :title="name">{{ nameLimited }}</span>
        <div class="input-files__file-size">{{ displaySize }}</div>
    </div>
</template>

<script>
import IconFileType from "@/Components/FileTypeIcons/IconFileType";

export default {
    components: {
        IconFileType,
    },

    props: {
        index: Number,
        type: String,
        name: String,
        size: Number,
    },

    emits: ['discard'],

    computed: {
        displaySize() {
            if (this.size > 1000000) {
                return Math.round(this.size / 1000 / 10) / 100 + ' Мб';
            }
            return Math.round(this.size / 10) / 100 + ' Кб';
        },
        nameLimited() {
            if (this.name.length > 38) {
                return this.name.substring(0, 37) + '...';
            }

            return this.name;
        },
    }
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

.input-files {
    &__file {
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
        flex-direction: column;

        &-icon {
            flex-grow: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            width: 70px;
            height: 70px;

            & > svg {
                width: 100%;
                height: 100%;
            }
        }

        &-name, &-size {
            flex-shrink: 0;
            flex-grow: 0;
            font-family: $project_font;
            text-align: center;
            padding: 5px 0 0;
            cursor: default;
        }

        &-name {
            font-size: 12px;
            line-height: 16px;
            max-height: 32px;
            word-break: break-all;
            overflow: hidden;
        }

        &-size {
            font-size: 12px;
            opacity: 0.7;
        }

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

    }
}
</style>
