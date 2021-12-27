<template>
    <label class="base-input" :class="{'base-input__differs': isDirty}">
        <span class="base-input__icon">
            <slot/>
        </span>
        <input
            class="base-input__input base-input__input-no-left base-input__input-no-right"
            :value="modelValue"
            :type="type"
            :required="required"
            :disabled="disabled"
            :autocomplete="autocomplete"
            @input="update"
            ref="input">
        <span class="base-input__clear" v-if="clearable" @click="setValue(null)"><icon-cross/></span>
    </label>
</template>

<script>
import empty from "../../Helpers/Lib/empty";
import IconCross from "../Icons/IconCross";

export default {
    components: {IconCross},
    props: {
        modelValue: {type: String, default: null},
        name: String,
        original: {type: String, default: null},

        required: {type: Boolean, default: false},
        disabled: {type: Boolean, default: false},
        valid: {type: Boolean, default: true},

        type: {type: String, default: 'text'},
        autocomplete: {type: String, default: 'off'},
    },

    emits: ['update:modelValue', 'changed'],

    computed: {
        clearable() {
            return this.modelValue !== null && this.modelValue !== ''
        },
        isDirty() {
            return empty(this.original) ? !empty(this.modelValue) : this.original !== this.modelValue;
        },
    },

    methods: {
        focus() {
            this.$refs.input.focus()
        },

        update(event) {
            this.setValue(event.target.value);
        },

        setValue(value) {
            if (value !== null) {
                if (this.type === 'number') {
                    value = Number(value);
                } else {
                    value = String(value);
                }
            }
            this.$emit('update:modelValue', value);
            this.$emit('changed', this.name, value);
        }
    }
}
</script>

