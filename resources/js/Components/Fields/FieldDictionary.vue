<template>
    <FieldWrapper :title="title" :hide-title="hideTitle" :required="required" :disabled="disabled" :valid="valid" :errors="errors">
        <DictionaryDropDown
            v-model="proxyValue"
            :name="name"
            :original="original"
            :valid="valid"
            :disabled="disabled"
            :has-null="hasNull"
            :dictionary="dictionary"
            :fresh="fresh"
            :disabled-options="disabledOptions"
            :identifier="identifier"
            :show="show"
            :search="search"
            :multi="multi"
            :top="top"
            :right="right"
            :center="center"
            :placeholder="placeholder"
            :small="small"
            @change="change"
            ref="input"
        />
    </FieldWrapper>
</template>

<script>
import FieldWrapper from "@/Components/Fields/Helpers/FieldWrapper";
import DictionaryDropDown from "@/Components/Inputs/DictionaryDropDown";

export default {
    components: {DictionaryDropDown, FieldWrapper},
    props: {
        name: String,
        modelValue: {type: [Boolean, String, Number, Object], default: null},
        original: {type: [Boolean, String, Number, Object], default: null},

        title: {type: String, default: null},
        required: {type: Boolean, default: false},
        disabled: {type: Boolean, default: false},
        valid: {type: Boolean, default: true},
        errors: {type: Array, default: null},
        hideTitle: {type: Boolean, default: false},

        placeholder: {type: String, default: null},
        small: {type: Boolean, default: false},

        hasNull: {type: Boolean, default: false},
        disabledOptions: {type: Boolean, default: false},
        identifier: {type: String, default: 'id'},
        show: {type: String, default: 'name'},

        multi: {type: Boolean, default: false},

        search: {type: Boolean, default: false},
        top: {type: Boolean, default: false},
        right: {type: Boolean, default: false},
        center: {type: Boolean, default: false},

        dictionary: String,
        fresh: {type: Boolean, default: false},
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

