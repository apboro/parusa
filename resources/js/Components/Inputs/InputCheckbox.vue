<template>
    <div class="input-checkbox">
        <CheckBox class="input-checkbox__input" :value="value" v-model="proxyValue" :valid="valid" :label="label" :disabled="disabled" :small="small"/>
    </div>
</template>

<script>
import CheckBox from "@/Components/Inputs/Helpers/CheckBox";
import empty from "@/Core/Helpers/Empty";

export default {
    components: {CheckBox},
    props: {
        name: String,
        modelValue: {type: [Number, String, Boolean, Array], default: null},
        original: {type: [Number, String, Boolean, Array], default: null},
        value: {type: [Number, String, Boolean], default: null},
        valid: {type: Boolean, default: true},
        disabled: {type: Boolean, default: false},
        label: {type: String, default: false},
        small: {type: Boolean, default: false},
    },

    computed: {
        isDirty() {
            if (this.modelValue instanceof Array) {
                return empty(this.original) ? this.modelValue.indexOf(this.value) !== -1 : this.original.indexOf(this.value) !== this.modelValue.indexOf(this.value);
            } else {
                return this.modelValue === (this.original === null || this.original === false);
            }
        },
        proxyValue: {
            get() {
                return this.modelValue;
            },
            set(value) {
                this.$emit('update:modelValue', value);
                this.$emit('change', value, this.name);
            }
        }
    }
}
</script>

<style lang="scss">
@import "../variables";

$base_size_unit: 35px !default;

.input-checkbox {
    height: $base_size_unit;

    &__input {
        width: 100%;
        padding: 0;
    }
}
</style>
