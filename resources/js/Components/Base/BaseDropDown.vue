<template>
    <div class="base-dropdown">
        <div class="base-dropdown__display" :class="{'base-dropdown__display-differs': isDirty}" @click="toggle">
            <span class="base-dropdown__display-value"
                  :class="{'base-dropdown__display-value-placeholder': value === null}">{{ value }}</span>
            <span class="base-dropdown__display-icon" :class="{'base-dropdown__display-icon-dropped':dropped}"><icon-dropdown/></span>
        </div>

        <div class="base-dropdown__list"
             :class="{'base-dropdown__list-shown': dropped, 'base-dropdown__list-top': toTop}"
        >
            <scroll-box :mode="'vertical'"
                        :scrollable-class="'base-dropdown__list-wrapper'"
            >
            <span class="base-dropdown__list-item" v-if="hasNull"
                  :class="{'base-dropdown__list-item-current' : modelValue === null}"
                  @click="value = null">{{ placeholder }}</span>
            <span class="base-dropdown__list-item" v-for="(val, key) in options"
                  :class="{'base-dropdown__list-item-current' : isCurrent(val)}"
                  :key="key"
                  @click="value = val"
            >{{ displayValue(val) }}</span>
            </scroll-box>
        </div>
    </div>
</template>

<script>
import IconDropdown from "../Icons/IconDropdown";
import empty from "../../Helpers/Lib/empty";
import ScrollBox from "../ScrollBox";

export default {
    props: {
        modelValue: {type: [Boolean, String, Number, Object], default: null},
        name: String,
        original: {type: [Boolean, String, Number, Object], default: null},

        placeholder: {type: String, default: null},
        hasNull: {type: Boolean, default: false},

        options: {type: Array, default: () => ([])},
        keyBy: {type: String, default: null},
        valueBy: {type: String, default: null},
        toTop: {type: Boolean, default: false},
    },

    emits: ['update:modelValue', 'changed', 'dropped'],

    components: {
        ScrollBox,
        IconDropdown,
    },

    data: () => ({
        dropped: false,
    }),

    computed: {
        value: {
            get() {
                if (this.modelValue === null) {
                    return this.placeholder;
                }

                if (this.keyBy !== null && this.valueBy !== null && !empty(this.options)) {
                    let current = null;
                    this.options.some((option => {
                        if (option[this.keyBy] === this.modelValue) {
                            current = option[this.valueBy];
                            return true;
                        }
                        return false;
                    }))

                    return current !== null ? current : this.modelValue;
                }

                return this.modelValue;
            },
            set(value) {
                if (
                    typeof value === "object" &&
                    value !== null &&
                    this.keyBy !== null &&
                    typeof value[this.keyBy] !== "undefined"
                ) {
                    value = value[this.keyBy];
                }
                this.$emit('update:modelValue', value);
                this.$emit('changed', this.name, value);
                this.close();
            }
        },

        isDirty() {
            return this.original !== this.modelValue;
        }
    },

    methods: {
        toggle() {
            if (this.dropped === true) {
                this.dropped = false;
                setTimeout(() => {
                    window.removeEventListener('click', this.close);
                }, 100);
            } else {
                this.dropped = true;
                this.$emit('dropped');
                this.$nextTick(() => {
                    const el = this.$el.querySelector('.base-dropdown__list');
                    el.style.height = null;
                    const height = el.clientHeight + 1;
                    el.style.height = height + 'px';
                    el.parentElement.focus();
                });
                setTimeout(() => {
                    window.addEventListener('click', this.close);
                }, 100);
            }
        },

        close() {
            window.removeEventListener('click', this.close);
            this.dropped = false;
        },

        isCurrent(value) {
            if (
                typeof value === "object" &&
                value !== null &&
                this.keyBy !== null &&
                typeof value[this.keyBy] !== "undefined"
            ) {
                return this.modelValue === value[this.keyBy]
            }

            return this.modelValue === value;
        },

        displayValue(value) {
            if (
                typeof value === "object" &&
                value !== null &&
                this.valueBy !== null &&
                typeof value[this.valueBy] !== "undefined"
            ) {
                value = value[this.valueBy]
            }

            return value
        },
    },
}
</script>

