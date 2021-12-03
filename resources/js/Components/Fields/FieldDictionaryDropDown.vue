<template>
    <div class="field" :class="{'field__required': required}">
        <span class="field__title">{{ title }}</span>
        <div class="field__input">
            <dictionary-drop-down
                :name="name"
                :dictionary="dictionary"
                :original="original"
                :required="required"
                :disabled="disabled"
                :valid="valid"
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
import DictionaryDropDown from "../Dictionary/DictionaryDropDown";

export default {
    props: {
        modelValue: {type: Number, default: null},
        title: String,
        name: String,
        original: {type: [Boolean, String, Number, Object], default: null},
        dictionary: String,

        required: {type: Boolean, default: false},
        disabled: {type: Boolean, default: false},
        valid: {type: Boolean, default: true},

        errors: {type: Array, default: () => ([])},
    },

    emits: ['update:modelValue', 'changed'],

    components: {
        DictionaryDropDown,
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
