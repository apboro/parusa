<template>
    <div class="field" :class="{'field__required': required}">
        <span class="field__title">{{ title }}</span>
        <div class="field__input">
            <base-files
                :name="name"
                :original="original"
                :required="required"
                :disabled="disabled"
                :valid="valid"
                :max-files="maxFiles"
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
import BaseFiles from "../Base/BaseFiles";

export default {
    props: {
        modelValue: {type: Array, default: () => ([])},
        title: String,
        name: String,
        original: {type: Array, default: () => ([])},

        required: {type: Boolean, default: false},
        disabled: {type: Boolean, default: false},
        valid: {type: Boolean, default: true},

        maxFiles: {type: Number, default: 0},

        errors: {type: Array, default: () => ([])},
    },

    emits: ['update:modelValue', 'changed'],

    components: {
        BaseFiles,
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
