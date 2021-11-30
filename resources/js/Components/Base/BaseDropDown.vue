<template>
    <div class="base-dropdown">
        <div class="base-dropdown__display" :class="{'base-dropdown__display-differs': differsFromInitial}" @click="toggle">
            <span class="base-dropdown__display-value"
                  :class="{'base-dropdown__display-value-placeholder': currentValue === null}">{{ currentValue }}</span>
            <span class="base-dropdown__display-icon" :class="{'base-dropdown__display-icon-dropped':dropped}"><icon-dropdown/></span>
        </div>

        <div class="base-dropdown__list" :class="{'base-dropdown__list-shown': dropped}">
                    <span class="base-dropdown__list-item" v-for="(value, key) in options"
                          :class="{'base-dropdown__list-item-current' : isCurrent(value)}"
                          :key="key"
                          @click="setValue(value)"
                    >{{ value }}</span>
        </div>
    </div>
</template>

<script>
import IconDropdown from "../Icons/IconDropdown";

export default {
    props: {
        modelValue: {
            type: [Boolean, String, Number, Object],
            default: null,
        },
        initial: {
            type: [Boolean, String, Number, Object],
            default: null,
        },
        options: {
            type: Array,
            default: () => ([]),
        },
        keyBy: {
            type: String,
            default: null,
        },
        valueBy: {
            type: String,
            default: null,
        },
        placeholder: {
            type: String,
            default: null,
        }
    },

    emits: ['update:modelValue'],

    components: {
        IconDropdown,
    },

    data: () => ({
        dropped: false,
    }),

    computed: {
        currentKey: function () {
            if (typeof this.modelValue === "object") {
                return (this.keyBy === null) || (typeof this.modelValue[this.keyBy] === "undefined") ? null : this.modelValue[this.keyBy];
            }

            return this.modelValue;
        },

        currentValue() {
            if (typeof this.modelValue === "object") {
                return (this.valueBy === null) || (typeof this.modelValue[this.valueBy] === "undefined") ? null : this.modelValue[this.valueBy];
            }

            return this.modelValue;
        },

        differsFromInitial() {
            return this.initial !== this.modelValue;
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
            if (typeof this.modelValue === "object") {
                return (this.keyBy === null) || (typeof this.modelValue[this.keyBy] === "undefined") ? false : this.modelValue[this.keyBy] === value[this.keyBy];
            }

            return this.modelValue === value;
        },

        setValue(value) {
            this.$emit('update:modelValue', value);
            this.close();
        },
    },
}
</script>

