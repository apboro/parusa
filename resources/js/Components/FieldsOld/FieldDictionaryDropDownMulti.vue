<template>
    <div class="field" :class="{'field__required': required}">
        <span class="field__title">{{ title }}</span>
        <div class="field__input">
            <dictionary-drop-down-multi
                :name="name"
                :dictionary="dictionary"
                :required="required"
                :disabled="disabled"
                :valid="valid"
                :original="original"
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
import DictionaryDropDownMulti from "../Dictionary/DictionaryDropDownMulti";

export default {
    props: {
        modelValue: {type: Array, default: () => ([])},
        title: String,
        name: String,
        original: {type: Array, default: () => ([])},
        dictionary: String,

        required: {type: Boolean, default: false},
        disabled: {type: Boolean, default: false},
        valid: {type: Boolean, default: true},

        errors: {type: Array, default: () => ([])},
    },

    emits: ['update:modelValue', 'changed'],

    components: {
        DictionaryDropDownMulti,
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
