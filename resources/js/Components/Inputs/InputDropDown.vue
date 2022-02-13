<template>
    <InputWrapper class="input-dropdown" :dirty="isDirty" :disabled="disabled" :valid="valid" :has-focus="dropped" :label="false"
                  :class="{'input-dropdown__disabled': disabled}"
    >
        <span class="input-dropdown__value"
              :class="{'input-dropdown__value-placeholder': this.modelValue === null && !this.hasNull, 'input-dropdown__value-small': small}"
              :title="value" @click="toggle">{{ value }}</span>
        <span class="input-dropdown__toggle" :class="{'input-dropdown__toggle-expanded': dropped}" @click.capture="toggle"><IconDropdown/></span>
        <div class="input-dropdown__list"
             :class="{'input-dropdown__list-shown': dropped, 'input-dropdown__list-top': top, 'input-dropdown__list-right': right, 'input-dropdown__list-center': center}"
             @click="false"
        >
            <div class="input-dropdown__list-search" v-if="search">
                <InputSearch v-model="terms" @change="updateHeight" @click.stop="false" ref="search"/>
            </div>
            <scroll-box :mode="'vertical'" :scrollable-class="'input-dropdown__list-wrapper'" v-if="dropped">
                <span class="input-dropdown__list-item" v-if="hasNull"
                      :class="{'input-dropdown__list-item-current' : modelValue === null}" @click="value = null">{{ placeholder }}</span>
                <span class="input-dropdown__list-item" v-for="(val, key) in displayableOptions"
                      :class="{'input-dropdown__list-item-current' : isCurrent(val['key'])}"
                      :key="key" @click="value = val['key']" v-html="displayValue(val['value'])"></span>
            </scroll-box>
        </div>
    </InputWrapper>
</template>

<script>
import empty from "@/Core/Helpers/Empty";
import IconDropdown from "@/Components/Icons/IconDropdown";
import ScrollBox from "@/Components/ScrollBox";
import InputSearch from "@/Components/Inputs/InputSearch";
import InputWrapper from "@/Components/Inputs/Helpers/InputWrapper";

export default {
    props: {
        name: String,
        modelValue: {type: [Boolean, String, Number, Object], default: null},
        original: {type: [Boolean, String, Number, Object], default: null},
        valid: {type: Boolean, default: true},
        disabled: {type: Boolean, default: false},

        hasNull: {type: Boolean, default: false},
        placeholder: {type: String, default: null},

        options: {type: Array, default: () => ([])},
        disabledOptions: {type: Boolean, default: false},
        identifier: {type: String, default: null},
        show: {type: String, default: null},

        search: {type: Boolean, default: false},
        top: {type: Boolean, default: false},
        right: {type: Boolean, default: false},
        center: {type: Boolean, default: false},
        small: {type: Boolean, default: false},
    },

    emits: ['update:modelValue', 'change', 'drop'],

    components: {InputWrapper, IconDropdown, ScrollBox, InputSearch},

    data: () => ({
        dropped: false,
        dropping: false,
        terms: null,
    }),

    computed: {
        isDirty() {
            return this.original !== this.modelValue;
        },
        displayableOptions() {
            let options = [];
            this.options.map((option, key) => {
                if (typeof option === "object" && option !== null && this.identifier !== null && this.show !== null &&
                    typeof option[this.identifier] !== "undefined" && typeof option[this.show] !== "undefined"
                ) {
                    const value = option[this.show];
                    if (
                        (this.disabledOptions || typeof option['enabled'] === "undefined" || Boolean(option['enabled']) === true) &&
                        (!this.search || empty(this.terms) || String(value).toLowerCase().search(this.terms.toLowerCase()) !== -1)
                    ) {
                        options.push({key: key, value: value});
                    }
                } else {
                    options.push({key: key, value: option});
                }
            });
            return options;
        },
        value: {
            get() {
                if (this.modelValue === null) return this.placeholder;
                if (this.identifier !== null && this.show !== null && !empty(this.options)) {
                    let current = null;
                    this.options.some(option => (option[this.identifier] === this.modelValue) && ((current = option[this.show]) || true));
                    return current !== null ? current : this.modelValue;
                }
                return this.modelValue;
            },
            set(key) {
                let value;
                if (key === null) {
                    value = null;
                } else {
                    value = this.options[key];
                    if (typeof value === "object" && value !== null &&
                        this.identifier !== null && this.show !== null &&
                        typeof value[this.identifier] !== "undefined") {
                        value = value[this.identifier];
                    }
                }
                this.$emit('update:modelValue', value);
                this.$emit('change', value, this.name);
                this.close();
            }
        },
    },

    methods: {
        isCurrent(key) {
            if (empty(this.options) || empty(this.modelValue)) {
                return false;
            }
            const option = this.options[key];
            if (typeof option === "object" && option !== null && this.identifier !== null && this.show !== null &&
                typeof option[this.identifier] !== "undefined" && typeof option[this.show] !== "undefined"
            ) {
                return this.modelValue === option[this.identifier];
            } else {
                return this.modelValue === option;
            }
        },
        displayValue(value) {
            if (this.search && this.terms) value = this.$highlight(value, this.terms);
            return value;
        },
        toggle() {
            if (this.disabled) return;
            if (this.dropped === true) {
                this.close();
            } else {
                this.$emit('drop');
                this.dropped = true;
                this.dropping = true;
                this.updateHeight();
                if (this.search) this.$nextTick(() => {
                    this.$refs.search.focus();
                });
                document.addEventListener('click', this.close);
            }
        },
        close() {
            if (this.dropping === true) {
                this.dropping = false;
            } else {
                document.removeEventListener('click', this.close);
                this.dropped = false;
            }
        },
        updateHeight() {
            this.$nextTick(() => {
                const el = this.$el.querySelector('.input-dropdown__list');
                el.style.height = null;
                el.style.width = null;
                el.style.height = (el.clientHeight + 1) + 'px';
                el.style.width = (el.clientWidth + 1) + 'px';
            });
        },
    },
}
</script>

<style lang="scss">
@use "sass:math";
@import "../variables";

$project_font: -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica Neue, Arial, Noto Sans, sans-serif, Apple Color Emoji, Segoe UI Emoji, Segoe UI Symbol, Noto Color Emoji !default;
$base_size_unit: 35px !default;
$animation_time: 150ms !default;
$animation: cubic-bezier(0.24, 0.19, 0.28, 1.29) !default;
$shadow_1: 0 1px 3px rgba(0, 0, 0, 0.12), 0 1px 2px rgba(0, 0, 0, 0.24) !default;
$input_color: #1e1e1e !default;
$input_placeholder_color: #757575 !default;
$input_background_color: #ffffff !default;
$base_primary_color: #0D74D7 !default;
$base_primary_hover_color: lighten(#0D74D7, 10%) !default;

.input-dropdown {
    height: $base_size_unit;

    &:not(&__disabled) {
        cursor: pointer;
    }

    &__value {
        background-color: transparent;
        color: inherit;
        cursor: inherit;
        display: flex;
        flex-grow: 1;
        font-family: $project_font;
        font-size: 16px;
        height: 100%;
        line-height: calc(#{$base_size_unit} - 2px);
        overflow: hidden;
        padding: 0 0 0 math.div($base_size_unit, 4);
        white-space: nowrap;

        &-small {
            font-size: 14px;
        }

        &-placeholder {
            color: $input_placeholder_color;
        }
    }

    &__toggle {
        align-items: center;
        box-sizing: border-box;
        cursor: inherit;
        display: flex;
        flex-grow: 0;
        flex-shrink: 0;
        height: 100%;
        justify-content: center;
        padding: math.div($base_size_unit, 4);
        width: $base_size_unit * 0.75;

        & > svg {
            transition: transform $animation $animation_time;
        }

        &-expanded {
            & > svg {
                transform: rotate(-180deg);
            }
        }
    }

    &__list {
        background-color: $input_background_color;
        bottom: -1px;
        box-shadow: $shadow_1;
        box-sizing: border-box;
        display: flex;
        flex-direction: column;
        left: -1px;
        max-height: $base_size_unit * 8;
        min-width: calc(100% + 2px);
        opacity: 0;
        padding: 0;
        position: absolute;
        transform: translateY(100%);
        transition: opacity $animation $animation_time, visibility $animation $animation_time;
        visibility: hidden;
        z-index: 10;

        &-shown {
            opacity: 1;
            visibility: visible;
        }

        &-top {
            bottom: unset;
            top: -1px;
            transform: translateY(-100%);
        }

        &-right {
            left: unset;
            right: -1px;
        }

        &-center {
            left: 50%;
            right: unset;
            transform: translate(-50%, 100%);
        }

        &-top#{&}-center {
            transform: translate(-50%, -100%);
        }

        &-search {
            box-sizing: border-box;
            margin: 5px;
        }

        &-wrapper {
            display: flex;
            flex-direction: column;
        }

        &-item {
            box-sizing: border-box;
            color: $input_color;
            cursor: pointer;
            display: block;
            font-family: $project_font;
            font-size: 14px;
            height: $base_size_unit * 0.8;
            line-height: $base_size_unit * 0.8;
            padding: 0 10px;
            transition: color $animation $animation_time;
            white-space: nowrap;

            &-current {
                color: $base_primary_color;
            }

            &:hover {
                color: $base_primary_hover_color;
            }

            &:first-child {
                margin-top: 5px;
            }

            &:last-child {
                margin-bottom: 5px;
            }
        }
    }
}
</style>
