<template>
    <FieldWrapper :title="title" :hide-title="hideTitle" :required="required" :disabled="disabled" :valid="valid" :errors="errors">
        <InputFiles
            v-model="proxyValue"
            :name="name"
            :original="original"
            :valid="valid"
            :disabled="disabled"
            :max-fiele="maxFiles"
            @change="change"
            ref="input"
        />
    </FieldWrapper>
</template>

<script>
import FieldWrapper from "@/Components/Fields/Helpers/FieldWrapper";
import InputFiles from "@/Components/Inputs/InputFiles";

export default {
    components: {InputFiles, FieldWrapper},
    props: {
        name: String,
        modelValue: {type: Array, default: null},
        original: {type: Array, default: null},

        title: {type: String, default: null},
        required: {type: Boolean, default: false},
        disabled: {type: Boolean, default: false},
        valid: {type: Boolean, default: true},
        errors: {type: Array, default: null},
        hideTitle: {type: Boolean, default: false},

        maxFiles: {type: Number, default: 0},
    },

    emits: ['update:modelValue', 'change'],

    computed: {
        proxyValue: {
            get() {
                return this.modelValue;
            },
            set(value) {
                this.$emit('update:modelValue', value);
            }
        }
    },

    methods: {
        focus() {
            this.$refs.input.focus()
        },
        change(value, name) {
            this.$emit('change', value, name);
        },
    }
}
</script>

