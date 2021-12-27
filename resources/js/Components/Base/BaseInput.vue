<template>
    <label class="base-input" :class="{'base-input__differs': isDirty, 'base-input__not-valid': !valid}">
        <input
            class="base-input__input"
            :value="modelValue"
            :type="type"
            :required="required"
            :disabled="disabled"
            :autocomplete="autocomplete"
            :placeholder="placeholder"
            @input="update"
            ref="input">
    </label>
</template>

<script>
import empty from "../../Helpers/Lib/empty";

export default {
    props: {
        modelValue: {type: [String, Number], default: null},
        name: String,
        original: {type: [String, Number], default: null},

        required: {type: Boolean, default: false},
        disabled: {type: Boolean, default: false},
        valid: {type: Boolean, default: true},

        type: {type: String, default: 'text'},
        autocomplete: {type: String, default: 'off'},
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
            let value;
            if (this.type === 'number') {
                value = Number(event.target.value);
            } else {
                value = String(event.target.value);
            }
            this.$emit('update:modelValue', value);
            this.$emit('changed', this.name, value);
        },
    }
}
</script>

