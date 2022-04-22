<template>
    <div class="ap-input-date__wrapper">
        <input class="ap-input-date__input"
               :class="{'ap-input-date__input-small': small}"
               v-model="display"
               :autocomplete="'off'"
               :disabled="disabled"
               :placeholder="placeholder"
               maxlength="10"
               @keydown="filterKeys"
               @click="showPicker"
               @focus="focus"
               @blur="blur"
               ref="input"
        />
        <span class="ap-input-date__clear" v-if="isClearable"
              :class="{'ap-input-date__clear-enabled': clearable && !disabled}"
              @click="clear"
        >
            <IconCross/>
        </span>
        <div class="ap-input-date__picker" :class="{'ap-input-date__picker-shown': picker}">
            <ShowcaseDatePicker
                :date="innerValue"
                :from="fromProxy"
                :to="toProxy"
                @selected="picked"
            />
        </div>
    </div>
</template>

<script>
import IconCross from "@/Components/Icons/IconCross";
import ShowcaseDatePicker from "@/Pages/Showcase/Components/Helpers/ShowcaseDatePicker";

export default {
    components: {ShowcaseDatePicker, IconCross},

    props: {
        modelValue: {type: String, default: null},
        from: {type: String, default: null},
        to: {type: String, default: null},

        placeholder: {type: String, default: null},
        disabled: {type: Boolean, default: false},

        small: {type: Boolean, default: false},
        clearable: {type: Boolean, default: false},
        pickOnClear: {type: Boolean, default: true},
    },

    emits: ['update:modelValue', 'focus', 'blur'],

    data: () => ({
        picker: false,
        dropping: false,
        isFocused: false,
        displayValue: null,
        innerValue: null,
    }),

    computed: {
        isClearable() {
            return this.clearable && this.modelValue !== null;
        },
        display: {
            get() {
                return this.displayValue;
            },
            set(value) {
                this.displayValue = value;
                if (value === '' || value === null) {
                    this.setInner(null);
                    this.set(null);
                    return;
                }
                const dateObject = value.split('.');
                const year = (typeof dateObject[2] !== "undefined" && dateObject[2] !== '') ? Number(dateObject[2]) : null;
                const month = (typeof dateObject[1] !== "undefined" && dateObject[1] !== '') ? Number(dateObject[1]) : null;
                const day = (typeof dateObject[0] !== "undefined" && dateObject[0] !== '') ? Number(dateObject[0]) : null;
                if (day !== null && month !== null && year !== null && String(year).length === 4 && month >= 1 && month <= 12) {
                    const date = new Date(year, month - 1, day);
                    if (date.getFullYear() === year && date.getMonth() + 1 === month && date.getDate() === day) {
                        this.setInner(date);
                        this.set(date);
                    }
                }
            }
        },
        fromProxy() {
            const date = this.from === null ? null : this.from.split('T');
            return date === null || date[0] === '' ? null : new Date(date[0]);
        },
        toProxy() {
            const date = this.to === null ? null : this.to.split('T');
            return date === null || date[0] === '' ? null : new Date(date[0]);
        },
    },

    watch: {
        modelValue(value) {
            this.setInner(value);
        }
    },

    created() {
        this.setInner(this.modelValue);
    },

    methods: {
        setInner(value, force = false) {
            this.innerValue = typeof value === "string" ? new Date(value) : value;
            if (!this.isFocused || force) {
                this.displayValue = this.innerValue === null
                    ? null
                    : String(this.innerValue.getDate()).padStart(2, '0') + '.' + String(this.innerValue.getMonth() + 1).padStart(2, '0') + '.' + String(this.innerValue.getFullYear()).padStart(4, '0')
            }
        },
        picked(value) {
            this.setInner(value, true);
            this.set(value);
            this.$refs.input.focus();
            this.$nextTick(() => {
                this.picker = false;
            });
        },
        set(value) {
            if (value === null) {
                this.$emit('update:modelValue', null);
            } else {
                const formatted = value.getFullYear() + '-' + String(value.getMonth() + 1).padStart(2, '0') + '-' + String(value.getDate()).padStart(2, '0');
                this.$emit('update:modelValue', formatted);
            }
        },
        focus() {
            this.$refs.input.focus();
            this.isFocused = true;
            this.$emit('focus');
        },
        blur() {
            this.setInner(this.innerValue);
            this.isFocused = this.picker;
            if (!this.isFocused) {
                this.$emit('blur');
            }
        },
        filterKeys(event) {
            const accepted = [
                20, // capslock
                17, // control
                18, // option
                16, // shift
                37, 38, 39, 40, // arrow keys
                9, // tab (let blur handle tab)
                8, //backspace
                46, // delete
            ];
            if (accepted.indexOf(event.keyCode) === -1 && ['1', '2', '3', '4', '5', '6', '7', '8', '9', '0', '.'].indexOf(event.key) === -1) {
                event.preventDefault();
            }
            return true;
        },
        clear() {
            if (this.isClearable && !this.disabled) {
                this.setInner(null, true);
                this.set(null);
                if (this.pickOnClear) {
                    this.showPicker(false);
                }
            }
            this.focus();
        },
        showPicker(dropping = true) {
            if (this.disabled || this.picker === true) return;
            this.picker = true;
            this.dropping = dropping;
            window.addEventListener('click', this.close);
        },
        close(event) {
            if (this.dropping === true || event && event.target === this.$refs.input) {
                this.dropping = false;
            } else {
                window.removeEventListener('click', this.close);
                this.picker = false;
                this.blur();
            }
        },
        addDays(value) {
            if (this.innerValue !== null) {
                this.innerValue.setDate(this.innerValue.getDate() + value);
                this.set(this.innerValue);
            }
        }
    }
}
</script>

<style lang="scss">
@use "sass:math";
@import "../../variables";

.ap-input-date {
    &__wrapper {
        height: 100%;
        width: 100%;
        display: flex;
    }

    &__input {
        background-color: transparent;
        border: none !important;
        box-sizing: border-box;
        color: $showcase_link_color;
        cursor: inherit;
        display: block;
        flex-grow: 1;
        flex-shrink: 1;
        font-family: $showcase_font;
        font-size: 16px;
        height: 100%;
        line-height: $showcase_size_unit;
        outline: none !important;
        padding: 0.15em 0 0 10px;
        width: 100%;
        font-weight: bold;

        &-small {
            font-size: 14px;
        }

        &::placeholder { /* Chrome, Firefox, Opera, Safari 10.1+ */
            color: $showcase_placeholder_color;
            opacity: 1; /* Firefox */
        }

        &:-ms-input-placeholder { /* Internet Explorer 10-11 */
            color: $showcase_placeholder_color;
        }

        &::-ms-input-placeholder { /* Microsoft Edge */
            color: $showcase_placeholder_color;
        }
    }

    &__clear {
        box-sizing: border-box;
        flex-grow: 0;
        flex-shrink: 0;
        height: 100%;
        padding: math.div($showcase_size_unit, 5);
        width: $showcase_size_unit;
        color: $showcase_primary_color;
        opacity: 0;
        transition: opacity $animation $animation_time;

        & > svg {
            height: 100%;
            width: 100%;
        }

        &-enabled {
            cursor: pointer;
            opacity: 0.6;

            &:hover {
                opacity: 1;
            }
        }
    }

    &__picker {
        position: absolute;
        left: -1px;
        bottom: -6px;
        transform: translateY(100%);
        box-sizing: border-box;
        padding: 12px 20px;
        border-radius: 2px;
        z-index: 50;
        background-color: $showcase_white_color;
        border: 1px solid $showcase_light_gray_color;
        opacity: 0;
        visibility: hidden;
        transition: opacity $animation $animation_time, visibility $animation $animation_time;

        &:before {
            content: '';
            display: block;
            background-color: $showcase_white_color;
            width: 6px;
            height: 6px;
            position: absolute;
            left: 14px;
            top: -4px;
            transform: rotate(45deg);
            border-color: $showcase_light_gray_color;
            border-style: solid;
            border-width: 1px 0 0 1px;
        }

        &-shown {
            opacity: 1;
            visibility: visible;
        }
    }
}
</style>
