<template>
    <div class="field" :class="{'field__required': required}">
        <span class="field__title">{{ title }}</span>
        <div class="field__input">
            <base-drop-down
                :name="name"
                :original="original"
                :options="options"
                :key-by="keyBy"
                :value-by="valueBy"
                :required="required"
                :disabled="disabled"
                :placeholder="placeholder"
                :has-null="hasNull"
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
import BaseDropDown from "../Base/BaseDropDown";

export default {
    props: {
        modelValue: {type: [Boolean, String, Number, Object], default: null},
        title: String,
        name: String,
        original: {type: [Boolean, String, Number, Object], default: null},

        options: {type: Array, default: () => ([])},
        keyBy: {type: String, default: null},
        valueBy: {type: String, default: null},
        placeholder: {type: String, default: null},
        hasNull: {type: Boolean, default: false},

        required: {type: Boolean, default: false},
        disabled: {type: Boolean, default: false},
        valid: {type: Boolean, default: true},

        errors: {type: Array, default: () => ([])},
    },

    emits: ['update:modelValue', 'changed'],

    components: {
        BaseDropDown,
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
