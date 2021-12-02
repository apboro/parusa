<template>
    <label class="base-input" :class="{'base-input__differs': isDirty}">
        <span class="base-input__icon">
            <slot/>
        </span>
        <input
            class="base-input__input base-input__input-no-left"
            :value="modelValue"
            :type="type"
            :required="required"
            :disabled="disabled"
            :autocomplete="autocomplete"
            @input="update"
            ref="input">
    </label>
</template>

<script>
import empty from "../../Helpers/Lib/empty";

export default {
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
        isDirty() {
            return empty(this.original) ? !empty(this.modelValue) : this.original !== this.modelValue;
        },
    },

    methods: {
        focus() {
            this.$refs.input.focus()
        },

        update(event) {
            const value = String(event.target.value);
            this.$emit('update:modelValue', value);
            this.$emit('changed', this.name, value);
        },
    }
}
</script>

