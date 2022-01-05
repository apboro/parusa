<template>
    <label class="base-input" :class="{'base-input__differs': isDirty, 'base-input__not-valid': !valid}">
        <masked-input
            v-model="proxyValue"
            :name="name"
            :required="required"
            :disabled="disabled"
            :original="original"
            :type="type"
            :autocomplete="autocomplete"
            :placeholder="placeholder"
            :mask="mask"
            :maskHint="maskHint"
            ref="input"
        />
    </label>
</template>

<script>
import MaskedInput from "./Parts/MaskedInput";

export default {
    components: {
        MaskedInput
    },

    props: {
        modelValue: {type: String, default: null},
        name: String,
        original: {type: String, default: null},

        required: {type: Boolean, default: false},
        disabled: {type: Boolean, default: false},
        valid: {type: Boolean, default: true},

        type: {type: String, default: 'text'},
        autocomplete: {type: String, default: 'off'},
        placeholder: {type: String, default: null},

        mask: {type: String, default: null},
        maskHint: {type: String, default: null},
    },

    emits: ['update:modelValue', 'changed'],

    computed: {
        isDirty() {
            return this.$refs.input ? this.$refs.input.isDirty : false;
        },

        proxyValue: {
            get() {
                return this.modelValue;
            },
            set(value) {
                this.$emit('update:modelValue', value);
                this.$emit('changed', this.name, value);
            }
        },
    },
}
</script>

