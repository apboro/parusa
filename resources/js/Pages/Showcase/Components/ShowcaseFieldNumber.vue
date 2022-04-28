<template>
    <ShowcaseFieldWrapper :title="title" :hide-title="hideTitle" :required="required" :disabled="disabled" :valid="valid" :errors="errors">
        <ShowcaseInputNumber
            v-model="proxyValue"
            :name="name"
            :original="original"
            :valid="valid"
            :disabled="disabled"
            :step="step"
            :quantity="quantity"
            :min="min"
            :max="max"
            :placeholder="placeholder"
            :small="small"
            :border="border"
            @change="change"
            ref="input"
        />
    </ShowcaseFieldWrapper>
</template>

<script>
import ShowcaseFieldWrapper from "@/Pages/Showcase/Components/Helpers/ShowcaseFieldWrapper";
import ShowcaseInputNumber from "@/Pages/Showcase/Components/ShowcaseInputNumber";

export default {
    components: {ShowcaseInputNumber, ShowcaseFieldWrapper},
    props: {
        name: String,
        modelValue: {type: Number, default: null},
        original: {type: Number, default: null},

        title: {type: String, default: null},
        required: {type: Boolean, default: false},
        disabled: {type: Boolean, default: false},
        valid: {type: Boolean, default: true},
        errors: {type: Array, default: null},
        hideTitle: {type: Boolean, default: false},

        placeholder: {type: String, default: null},
        small: {type: Boolean, default: false},

        step: {type: Number, default: 1},
        quantity: {type: Boolean, default: false},
        min: {type: Number, default: null},
        max: {type: Number, default: null},
        border: {type: Boolean, default: true},
    },

    emits: ['update:modelValue', 'change'],

    computed: {
        proxyValue: {
            get() {
                return this.modelValue
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

