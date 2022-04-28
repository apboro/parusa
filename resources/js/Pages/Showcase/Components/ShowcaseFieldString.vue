<template>
    <ShowcaseFieldWrapper :title="title" :hide-title="hideTitle" :required="required" :disabled="disabled" :valid="valid" :errors="errors">
        <ShowcaseInputString
            v-model="proxyValue"
            :name="name"
            :original="original"
            :valid="valid"
            :disabled="disabled"
            :type="type"
            :autocomplete="autocomplete"
            :placeholder="placeholder"
            :small="small"
            @change="change"
            ref="input"
        />
    </ShowcaseFieldWrapper>
</template>

<script>
import ShowcaseFieldWrapper from "@/Pages/Showcase/Components/Helpers/ShowcaseFieldWrapper";
import ShowcaseInputString from "@/Pages/Showcase/Components/ShowcaseInputString";

export default {
    components: {ShowcaseInputString, ShowcaseFieldWrapper},
    props: {
        name: String,
        modelValue: {type: String, default: null},
        original: {type: String, default: null},

        title: {type: String, default: null},
        required: {type: Boolean, default: false},
        disabled: {type: Boolean, default: false},
        valid: {type: Boolean, default: true},
        errors: {type: Array, default: null},
        hideTitle: {type: Boolean, default: false},

        placeholder: {type: String, default: null},
        small: {type: Boolean, default: false},

        type: {type: String, default: 'text', validation: (value) => ['text', 'password'].indexOf(value) !== -1},
        autocomplete: {type: String, default: 'off'},
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

