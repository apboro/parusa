<template>
    <div class="ap-dialogs__overlay" v-if="shown" :class="{'ap-dialogs__overlay-hide': hiding, 'ap-dialogs__overlay-shown': showing}" @click="popupClose">
        <div class="ap-dialogs__dialog">
            <div class="ap-dialogs__dialog-header">
                <div class="ap-dialogs__dialog-header-title"><span v-if="title && !processing">{{ title }}</span></div>
                <div class="ap-dialogs__dialog-header-close" @click="resolve('close')">
                    <IconCross/>
                </div>
            </div>
            <div class="ap-dialogs__dialog-container">
                <ShowcaseV2LoadingProgress :loading="processing">
                    <template v-if="!processing">
                        <slot/>
                    </template>
                </ShowcaseV2LoadingProgress>
            </div>
            <div class="ap-dialogs__dialog-buttons" :class="'ap-dialogs__dialog-buttons-' + align">
                <ShowcaseV2Button v-for="button in buttons"
                           :color="button.color"
                           :identifier="button.result"
                           :disabled="button.disabled"
                           @clicked="resolve"
                >
                    {{ button.caption }}
                </ShowcaseV2Button>
            </div>
        </div>
    </div>
</template>

<script>
import ShowcaseV2Button from "@/Pages/ShowcaseV2/Components/ShowcaseV2Button";
import ShowcaseV2LoadingProgress from "@/Pages/ShowcaseV2/Components/ShowcaseV2LoadingProgress";
import IconCross from "@/Components/Icons/IconCross";

export default {
    components: {
        IconCross,
        ShowcaseV2LoadingProgress,
        ShowcaseV2Button,
    },
    props: {
        title: {type: String, default: null},
        message: {type: String, default: null},
        buttons: {type: Array, default: () => ([{result: 'ok', caption: 'OK'}])},
        align: {type: String, default: 'center'},
        manual: {type: Boolean, default: false},
        resolving: {type: Function, default: null},
        closeOnOverlay: {type: Boolean, default: false},
        verticalAlignCenter: {type: Boolean, default: false},
    },

    data: () => ({
        shown: false,
        resolve_function: null,
        showing: false,
        hiding: false,
        processing: false,
    }),

    methods: {
        show() {
            this.processing = false;
            this.showing = true;
            this.hiding = false;
            this.shown = true;
            setTimeout(() => {
                this.showing = true;
            }, 100);
            return new Promise(resolve => {
                this.resolve_function = resolve;
            });
        },

        hide() {
            this.hiding = true;
            setTimeout(() => {
                this.shown = false;
                this.showing = false;
                this.hiding = false;
                this.processing = false;
            }, 300);
        },

        resolve(value) {
            if (typeof this.resolve_function === "function") {
                this.resolve_function(value);
                this.hide();
            }
        },

        process(value) {
            this.processing = value;
        },

        popupClose(event) {
            if (event.target === this.$el) {
                this.resolve(null);
            }
        },
    }
}
</script>

<style lang="scss" scoped>
@import "../variables";

.ap-dialogs {
    &__overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 9900;
        backdrop-filter: blur(1px);
        background-color: transparentize($showcase_link_color, 0.35);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        visibility: hidden;
        transition: opacity $animation $animation_time, visibility $animation $animation_time;

        &-shown {
            opacity: 1;
            visibility: visible;
        }

        &-hide {
            opacity: 0;
            visibility: hidden;
        }
    }

    &__dialog {
        display: flex;
        flex-direction: column;
        max-height: 95%;
        max-width: 95%;
        min-width: 200px;
        box-sizing: border-box;
        padding: 30px 15px 30px 30px;
        background-color: $showcase_white_color;
        border-radius: 2px;
        margin: auto;
        overflow-y: auto;

        &-header {
            display: flex;
            flex-grow: 0;
            flex-shrink: 0;
            align-items: flex-start;
            justify-content: flex-start;
            box-sizing: border-box;
            padding: 0 15px 20px 0;

            &-title {
                font-family: $showcase_font;
                font-size: 24px;
                font-weight: bold;
                text-align: left;
                color: $showcase_link_color;
                flex-grow: 1;
            }

            &-close {
                flex-grow: 0;
                flex-shrink: 0;
                width: 20px;
                cursor: pointer;
                color: $showcase_link_color;
                opacity: 0.7;
                transition: opacity $animation $animation_time;
                margin-left: 15px;

                &:hover {
                    opacity: 1;
                }
            }
        }

        &-container {
            max-width: 1000px;
            overflow-x: hidden;
            overflow-y: auto;
            flex-grow: 1;
            flex-shrink: 1;
            padding: 0 15px 0 0;
            /* W3C standard - сейчас только для Firefox */
            scrollbar-color: #8c82ce #ededed;
            scrollbar-width: thin;
            min-height: 100px;

            /* для Chrome/Edge/Safari */
            &::-webkit-scrollbar {
                height: 5px;
                width: 5px;
            }

            &::-webkit-scrollbar-track {
                background: #ededed;
            }

            &::-webkit-scrollbar-thumb {
                background-color: #8c82ce;
                border-radius: 2px;
            }
        }

        &-buttons {
            display: flex;
            flex-grow: 0;
            flex-shrink: 0;
            align-items: center;
            justify-content: center;
            box-sizing: border-box;
            padding: 20px 15px 0 0;

            & > * {
                min-width: 100px;
            }
        }

    }
}
@media (max-width: 767px) {
    .dialogs__dialog-wrapper {
        padding: 0;
    }

    .dialogs__dialog-title {
        margin-bottom: 0;
    }
}
</style>
