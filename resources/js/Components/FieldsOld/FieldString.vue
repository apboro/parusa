<template>
    <div class="form-field__wrapper">
        <label class="form-field">
            <span class="form-field__title" v-if="title"
            ><span v-if="required" class="form-field__title-required">*</span>{{ title }}</span>
            <input
                class="form-field__input"
                :class="{'form-field__input-error': !valid}"
                :value="modelValue"
                :type="type"
                :required="required"
                :autocomplete="autocomplete"
                @input="update"
                ref="input">
            <span v-if="!valid" class="form-field__error">{{ error }}</span>
        </label>
    </div>
</template>

<script>
export default {
    props: {
        modelValue: {type: String, default: null},
        title: {type: String, default: null},
        name: {type: String, default: null},
        type: {type: String, default: 'text'},
        required: {type: Boolean, default: false},
        disabled: {type: Boolean, default: false},
        valid: {type: Boolean, default: true},
        error: {type: String, default: null},
        autocomplete: {type: String, default: 'off'},
    },

    emits: ['update:modelValue', 'changed'],

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

