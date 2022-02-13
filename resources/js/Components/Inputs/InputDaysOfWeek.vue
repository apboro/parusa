<template>
    <InputWrapper class="input-days" :label="false" :dirty="isDirty" :disabled="disabled" :valid="valid">
        <CheckBox :value="1" v-model="proxyValue" :label="'Пн'" :disabled="disabled" :small="small"/>
        <CheckBox :value="2" v-model="proxyValue" :label="'Вт'" :disabled="disabled" :small="small"/>
        <CheckBox :value="3" v-model="proxyValue" :label="'Ср'" :disabled="disabled" :small="small"/>
        <CheckBox :value="4" v-model="proxyValue" :label="'Чт'" :disabled="disabled" :small="small"/>
        <CheckBox :value="5" v-model="proxyValue" :label="'Пт'" :disabled="disabled" :small="small"/>
        <CheckBox :value="6" v-model="proxyValue" :label="'Сб'" :disabled="disabled" :small="small"/>
        <CheckBox :value="7" v-model="proxyValue" :label="'Вс'" :disabled="disabled" :small="small"/>
    </InputWrapper>
</template>

<script>

import InputWrapper from "@/Components/Inputs/Helpers/InputWrapper";
import CheckBox from "@/Components/Inputs/Helpers/CheckBox";
import empty from "@/Core/Helpers/Empty";

export default {
    components: {CheckBox, InputWrapper},
    props: {
        name: String,
        modelValue: {type: Array, default: null},
        original: {type: Array, default: null},
        valid: {type: Boolean, default: true},
        disabled: {type: Boolean, default: false},
        small: {type: Boolean, default: false},
    },

    emits: ['update:modelValue', 'change'],

    computed: {
        isDirty() {
            return empty(this.original) ? !empty(this.modelValue) : this.original !== this.modelValue;
        },
        proxyValue: {
            get() {
                return this.modelValue !== null ? this.modelValue : [];
            },
            set(value) {
                this.update(value);
            }
        }
    },

    methods: {
        update(value) {
            value.sort();
            this.$emit('update:modelValue', value);
            this.$emit('change', value, this.name);
        },
    }
}
</script>

<style lang="scss">
@import "../variables";

$base_size_unit: 35px !default;

.input-days {
    height: $base_size_unit;
    padding: 0 10px;
    cursor: default;
}
</style>
