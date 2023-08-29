<template>
    <div class="input-checkbox">
        <ShowcaseCheckBox class="input-checkbox__input" :value="value" v-model="proxyValue" :valid="valid" :label="label" :disabled="disabled" :small="small" :big="big">
            <slot/>
        </ShowcaseCheckBox>
    </div>
</template>

<script>
import empty from "@/Core/Helpers/Empty";
import ShowcaseCheckBox from "@/Pages/Showcase/Components/Helpers/ShowcaseCheckBox";

export default {
    components: {ShowcaseCheckBox},
    props: {
        name: String,
        modelValue: {type: [Number, String, Boolean, Array], default: null},
        original: {type: [Number, String, Boolean, Array], default: null},
        value: {type: [Number, String, Boolean], default: null},
        valid: {type: Boolean, default: true},
        disabled: {type: Boolean, default: false},
        label: {type: String, default: null},
        small: {type: Boolean, default: false},
        big: {type: Boolean, default: false},
    },

    emits: ['update:modelValue', 'change'],

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

<style lang="scss" scoped>
@import "../variables";

.input-checkbox {
    min-height: $showcase_size_unit;
    display: flex;
    align-items: center;

    &__input {
        width: 100%;
        padding: 0;
    }
}
</style>
