<template>
    <div class="dialogs__overlay" v-if="shown" :class="{'dialogs__overlay-hide': hiding, 'dialogs__overlay-shown': showing}" @click="popupClose">
        <div class="dialogs__dialog">
            <LoadingProgress :loading="processing">
                <div class="dialogs__dialog-wrapper">
                    <div class="dialogs__dialog-title" v-if="title">{{ title }}</div>
                    <div class="dialogs__dialog-message" v-if="message">
                        <div class="dialogs__dialog-message-text">{{ message }}</div>
                    </div>
                    <slot/>
                    <div class="dialogs__dialog-buttons" :class="'dialogs__dialog-buttons-' + align">
                        <GuiButton v-for="button in buttons"
                                   :color="button.color"
                                   :identifier="button.result"
                                   :disabled="button.disabled"
                                   @clicked="resolve"
                        >
                            {{ button.caption }}
                        </GuiButton>
                    </div>
                </div>
            </LoadingProgress>
        </div>
    </div>
</template>

<script>
import LoadingProgress from "@/Components/LoadingProgress";
import GuiButton from "@/Components/GUI/GuiButton";

export default {
    components: {GuiButton, LoadingProgress},
    props: {
        title: {type: String, default: null},
        message: {type: String, default: null},
        buttons: {type: Array, default: () => ([{result: 'ok', caption: 'OK'}])},
        align: {type: String, default: 'center'},
        manual: {type: Boolean, default: false},
        resolving: {type: Function, default: null},
        closeOnOverlay: {type: Boolean, default: false},
    },

    data: () => ({
        shown: false,
        resolve_function: null,
        showing: false,
        hiding: false,
        processing: false,
    }),

    methods: {
        show(fixed = false) {
            this.processing = false;
            this.showing = true;
            this.hiding = false;
            this.shown = true;
            setTimeout(() => {
                this.showing = true;
                if (fixed) {
                    this.fixSize();
                }
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
            if (this.resolving === null || (this.resolving(value) !== false)) {
                if (typeof this.resolve_function === "function") {
                    this.resolve_function(value);
                    if (!this.manual) {
                        this.hide();
                    }
                }
            }
        },

        process(value) {
            this.processing = value;
        },

        popupClose(event) {
            if (this.closeOnOverlay && event.target === this.$el) {
                this.resolve(null);
            }
        },

        fixSize() {
            const dialog = this.$el.querySelector('.dialogs__dialog');
            dialog.style.height = null;
            dialog.style.width = null;
            dialog.style.height = (dialog.clientHeight + 1) + 'px';
            dialog.style.width = (dialog.clientWidth + 1) + 'px';
        }
    }
}
</script>

<style lang="scss">

$project_font: -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica Neue, Arial, Noto Sans, sans-serif, Apple Color Emoji, Segoe UI Emoji, Segoe UI Symbol, Noto Color Emoji !default;
$animation_time: 150ms !default;
$animation: cubic-bezier(0.24, 0.19, 0.28, 1.29) !default;
$shadow_2: 0 3px 6px rgba(0, 0, 0, 0.16), 0 3px 6px rgba(0, 0, 0, 0.23) !default;
$base_white_color: #ffffff !default;
$base_black_color: #1e1e1e !default;
$base_red_color: #EA1B00 !default;
$base_green_color: #00AF2E !default;
$base_primary_color: #0D74D7 !default;
$base_orange_color: #EA8B00 !default;
$base_light_gray_color: #e5e5e5 !default;

.dialogs {
    &__overlay {
        position: fixed;
        z-index: 300;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        backdrop-filter: blur(1px);
        background-color: rgba(0, 0, 0, 0.2);
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
        background-color: $base_white_color;
        border-radius: 2px;
        box-sizing: border-box;
        padding: 15px;
        box-shadow: $shadow_2;
        max-height: 95%;

        &-wrapper {
            box-sizing: border-box;
            padding: 15px;
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        &-title {
            display: block;
            font-family: $project_font;
            font-size: 16px;
            font-weight: bold;
            text-align: left;
            color: $base_black_color;
            margin-bottom: 20px;
        }

        &-message {
            display: flex;
            flex-direction: row;
            align-items: center;
            margin-bottom: 20px;

            &-icon {
                width: 32px;
                height: 32px;
                margin-right: 30px;
                color: $base_black_color;

                &-red {
                    color: $base_red_color;
                }

                &-green {
                    color: $base_green_color;
                }

                &-blue {
                    color: $base_primary_color;
                }

                &-orange {
                    color: $base_orange_color;
                }
            }

            &-text {
                font-family: $project_font;
                font-size: 14px;
                color: $base_black_color;
                text-align: left;
                flex-grow: 1;
            }
        }

        &-buttons {
            margin-top: 30px;
            box-sizing: border-box;
            padding-top: 20px;
            border-top: 1px solid $base_light_gray_color;

            &-left {
                text-align: left
            }

            &-center {
                text-align: center
            }

            &-right {
                text-align: right
            }

            & > * {
                min-width: 100px;
            }
        }
    }
}
</style>
