<template>
    <div class="field" :class="{'field__required': required}">
        <span class="field__title">{{ title }}</span>
        <div class="field__input">
            <base-masked-input
                :name="name"
                :original="original"
                :required="required"
                :disabled="disabled"
                :valid="valid"
                :type="type"
                :autocomplete="autocomplete"
                :mask="mask"
                :mask-hint="maskHint"
                :placeholder="title"
                v-model="proxyValue"
                @changed="changed"
            />
            <div class="field__errors">
                <span class="field__errors-error" v-if="!valid" v-for="error in errors">{{ error }}</span>
            </div>
        </div>
    </div>
</template>

<script>
import BaseMaskedInput from "../Base/BaseMaskedInput";

export default {
    props: {
        modelValue: {type: String, default: null},
        title: String,
        name: String,
        original: {type: String, default: null},

        required: {type: Boolean, default: false},
        disabled: {type: Boolean, default: false},
        valid: {type: Boolean, default: true},

        type: {type: String, default: 'text'},
        autocomplete: {type: String, default: 'off'},

        errors: {type: Array, default: () => ([])},

        mask: {type: String, default: null},
        maskHint: {type: String, default: null},
    },

    emits: ['update:modelValue', 'changed'],

    components: {
        BaseMaskedInput,
    },

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
        changed(name, value) {
            this.$emit('changed', name, value);
        },
    },
}
</script>
