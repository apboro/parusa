<template>
    <ShowcaseV2RadioWrapper class="ap-radio-date" :dirty="isDirty" :disabled="disabled" :valid="valid">
        <ShowcaseV2DateRadio
            v-model="proxyValue"
            :from="from"
            :to="to"
            :dates="dates"
            :ischecked="ischecked"
            :placeholder="placeholder"
            :disabled="disabled"
            :small="small"
            :clearable="clearable"
            :pickOnClear="pickOnClear"
            @focus="isFocused = true"
            @blur="isFocused = false"
            ref="input"
        />
    </ShowcaseV2RadioWrapper>
</template>

<script>
import ShowcaseV2RadioWrapper from "@/Pages/ShowcaseV2/Components/Helpers/ShowcaseV2RadioWrapper";
import ShowcaseV2DateRadio from "@/Pages/ShowcaseV2/Components/Helpers/ShowcaseV2DateRadio";

export default {
    components: {ShowcaseV2DateRadio, ShowcaseV2RadioWrapper},

    props: {
        name: String,
        modelValue: {type: String, default: null},
        from: {type: String, default: null},
        to: {type: String, default: null},
        original: {type: String, default: null},
        valid: {type: Boolean, default: true},
        disabled: {type: Boolean, default: false},
        placeholder: {type: String, default: null},
        checked: {type: String, default: null},
        ischecked: {type: Boolean, default: true},
        dates: {type: Array, default: null},
        small: {type: Boolean, default: false},
        clearable: {type: Boolean, default: false},
        pickOnClear: {type: Boolean, default: true},
    },

    emits: ['update:modelValue', 'change', 'search'],

    data: () => ({
        isFocused: false,
        search_parameters: {
            date: null,
            persons: null,
            programs: null,
        },
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
                this.search_parameters.date = value;
                this.$emit('search', this.search_parameters);
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
