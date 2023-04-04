<template>
    <ShowcaseRadioWrapper class="ap-radio-date" :dirty="isDirty" :disabled="disabled" :valid="valid">
        <ShowcaseDateRadio
            v-model="proxyValue"
            :from="from"
            :to="to"
            :dates="dates"
            :checked="checked"
            :placeholder="placeholder"
            :disabled="disabled"
            :small="small"
            :clearable="clearable"
            :pickOnClear="pickOnClear"
            @focus="isFocused = true"
            @blur="isFocused = false"
            ref="input"
        />
    </ShowcaseRadioWrapper>
</template>

<script>
import ShowcaseRadioWrapper from "@/Pages/Showcase/Components/Helpers/ShowcaseRadioWrapper";
import ShowcaseDateRadio from "@/Pages/Showcase/Components/Helpers/ShowcaseDateRadio";

export default {
    components: {ShowcaseDateRadio, ShowcaseRadioWrapper},

    props: {
        name: String,
        modelValue: {type: String, default: null},
        from: {type: String, default: null},
        to: {type: String, default: null},
        original: {type: String, default: null},
        valid: {type: Boolean, default: true},
        disabled: {type: Boolean, default: false},
        placeholder: {type: String, default: null},
        checked: {type: Boolean, default: true},
        dates: {type: Array, default: null},
        small: {type: Boolean, default: false},
        clearable: {type: Boolean, default: false},
        pickOnClear: {type: Boolean, default: true},
    },

    emits: ['update:modelValue', 'change'],

    data: () => ({
        isFocused: false,
    }),

    computed: {
        isDirty() {
            return this.original !== this.modelValue;
        },
        proxyValue: {
            get() {
                return this.modelValue;
            },
            set(value) {
                this.$emit('update:modelValue', value);
                this.$emit('change', value, this.name);
            }
        },
    },
}
</script>

<style lang="scss" scoped>
@import "../variables";

.ap-input-date {
    height: $showcase_size_unit;
}
</style>
