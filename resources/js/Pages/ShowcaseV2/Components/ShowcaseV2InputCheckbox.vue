<template>
    <div class="input-checkbox">
        <ShowcaseV2Radio
            class="input-checkbox__input"
            :title="title"
            :description="description"
            :value="value"
            :checked="checked"
            :ischecked="ischecked"
            v-model="proxyValue"
        >
        </ShowcaseV2Radio>
    </div>
</template>

<script>
import empty from "@/Core/Helpers/Empty";
import ShowcaseV2Radio from "@/Pages/ShowcaseV2/Components/Helpers/ShowcaseV2Radio.vue";

export default {
    components: {ShowcaseV2Radio},
    props: {
        name: String,
        modelValue: {type: [Number, String, Boolean, Array], default: null},
        original: {type: [Number, String, Boolean, Array], default: null},
        value: {type: [Number, String, Boolean], default: null},
        title: {type: [String], default: null},
        description: {type: [String], default: null},
        valid: {type: Boolean, default: true},
        checked: {type: String, default: null},
        ischecked: {type: Boolean, default: true},
        disabled: {type: Boolean, default: false},
        label: {type: String, default: null},
        small: {type: Boolean, default: false},
    },

    emits: ['search', 'update:modelValue', 'change'],

    data: () => ({
        search_parameters: {
            date: null,
            persons: null,
            programs: null,
        },
    }),

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
                this.search_parameters.date = value;
                this.$emit('search', this.search_parameters);
            }
        },
    },
}
</script>

<style lang="scss" scoped>
@import "../../Showcase/variables";

.input-checkbox {
    min-height: $showcase_size_unit;
    display: flex;
    align-items: center;
    border: 1px solid $showcase_light_gray_color;
    border-radius: 2px;

    &__input {
        width: 100%;
        padding: 0;
    }
}

</style>
