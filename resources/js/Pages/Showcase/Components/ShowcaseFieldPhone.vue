<template>
    <ShowcaseFieldWrapper :title="title" :hide-title="hideTitle" :required="required" :disabled="disabled" :valid="valid" :errors="errors">
        <ShowcaseInputPhone
            v-model="proxyValue"
            :name="name"
            :original="original"
            :valid="valid"
            :disabled="disabled"
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
import ShowcaseInputPhone from "@/Pages/Showcase/Components/ShowcaseInputPhone";

export default {
    components: {ShowcaseInputPhone, ShowcaseFieldWrapper},
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

