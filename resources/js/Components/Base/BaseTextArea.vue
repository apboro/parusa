<template>
    <label class="base-textarea" :class="{'base-input__differs': isDirty, 'base-input__not-valid': !valid}">
        <textarea
            class="base-textarea__input"
            :value="modelValue"
            :required="required"
            :disabled="disabled"
            :placeholder="placeholder"
            @input="update"
            ref="input"/>
    </label>
</template>

<script>
import empty from "@/Core/Helpers/Empty";

export default {
    props: {
        modelValue: {type: String, default: null},
        name: String,
        original: {type: String, default: null},

        required: {type: Boolean, default: false},
        disabled: {type: Boolean, default: false},
        valid: {type: Boolean, default: true},

        placeholder: {type: String, default: null},
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

