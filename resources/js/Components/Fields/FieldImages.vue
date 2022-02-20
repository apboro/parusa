<template>
    <FieldWrapper :title="title" :hide-title="hideTitle" :required="required" :disabled="disabled" :valid="valid" :errors="errors">
        <InputImages
            v-model="proxyValue"
            :name="name"
            :original="original"
            :valid="valid"
            :disabled="disabled"
            :max-images="maxImages"
            @change="change"
            ref="input"
        />
    </FieldWrapper>
</template>

<script>
import FieldWrapper from "@/Components/Fields/Helpers/FieldWrapper";
import InputImages from "@/Components/Inputs/InputImages";

export default {
    components: {InputImages, FieldWrapper},
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

        maxImages: {type: Number, default: 0},
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

