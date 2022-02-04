<template>
    <div class="files-list">
        <template v-for="file in files">
            <a class="files-list__file" v-if="file['url']" :href="file['url']" :title="file['name']" target="_blank">
                <span class="files-list__file-icon">
                    <IconFileType :type="file['type']"/>
                </span>
                <span class="files-list__file-name">{{ nameLimited(file['name']) }}</span>
            </a>
            <span class="files-list__file" v-else :title="file['name']">
                <span class="files-list__file-icon">
                    <IconFileType :type="file['type']"/>
                </span>
                <span class="files-list__file-name">{{ nameLimited(file['name']) }}</span>
            </span>
        </template>
    </div>
</template>

<script>
import IconFileType from "@/Components/FileTypeIcons/IconFileType";

export default {
    components: {IconFileType},
    props: {
        files: {type: Array, default: () => ([])}
    },
    methods: {
        nameLimited(name) {
            if (name.length > 32) {
                return name.substring(0, 31) + '...';
            }

            return name;
        },
    }
}
</script>

<style lang="scss">
@import "../variables";

$project_font: -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica Neue, Arial, Noto Sans, sans-serif, Apple Color Emoji, Segoe UI Emoji, Segoe UI Symbol, Noto Color Emoji !default;
$animation_time: 150ms !default;
$animation: cubic-bezier(0.24, 0.19, 0.28, 1.29) !default;
$animation_bounce: cubic-bezier(0.24, 0.19, 0.63, 2.26) !default;
$base_size_unit: 35px !default;
$base_black_color: #1e1e1e !default;
$base_primary_color: #1660ad !default;
$base_primary_hover_color: #1e87f0 !default;

.files-list {
    width: 100%;

    &__file {
        display: inline-flex;
        vertical-align: top;
        width: $base_size_unit * 4;
        height: $base_size_unit * 4;
        box-sizing: border-box;
        padding: 5px;
        justify-content: center;
        align-items: center;
        border-radius: 2px;
        flex-direction: column;
        text-decoration: none;
        color: $base_black_color;

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
                transition: transform $animation_bounce $animation_time;
            }
        }

        &-name {
            flex-shrink: 0;
            flex-grow: 0;
            font-family: $project_font;
            text-align: center;
            font-size: 12px;
            word-break: break-all;
            line-height: 16px;
            height: 32px;
            overflow: hidden;
        }
    }
}

a.files-list__file .files-list__file-name {
    color: $base_primary_color;
    transition: color $animation $animation_time;
}

a.files-list__file:hover .files-list__file-name {
    color: $base_primary_hover_color;
}

a.files-list__file:hover .files-list__file-icon > svg {
    transform: scale(1.1);
}
</style>
