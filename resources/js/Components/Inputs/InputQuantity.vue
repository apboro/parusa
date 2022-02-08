<template>
    <div class="input-quantity">
        <GuiIconButton class="input-quantity__decrease" :border="false" @click.stop.prevent="decrease">
            <IconMinus/>
        </GuiIconButton>
        <InputNumber
            :name="name"
            v-model="valueProxy"
            :original="original"
            :valid="valid"
            :disabled="disabled"
            :placeholder="placeholder"
            ref="input"
        />
        <GuiIconButton class="input-quantity__increase" :border="false" @click.stop.prevent="increase">
            <IconPlus/>
        </GuiIconButton>
    </div>
</template>

<script>
import InputNumber from "@/Components/Inputs/InputNumber";
import GuiIconButton from "@/Components/GUI/GuiIconButton";
import IconMinus from "@/Components/Icons/IconMinus";
import IconPlus from "@/Components/Icons/IconPlus";

export default {
    components: {IconPlus, IconMinus, InputNumber, GuiIconButton},
    props: {
        name: String,
        modelValue: {type: Number, default: 0},
        original: {type: Number, default: null},
        valid: {type: Boolean, default: true},
        disabled: {type: Boolean, default: false},

        placeholder: {type: String, default: null},

        step: {type: Number, default: 1},
    },

    emits: ['update:modelValue', 'change'],

    computed: {
        valueProxy: {
            get() {
                return this.modelValue;
            },
            set(value) {
                this.$emit('update:modelValue', value);
            }
        },
    },

    methods: {
        focus() {
            this.$refs.input.focus()
        },

        change(value) {
            this.$emit('change', value, this.name);
        },

        decrease() {
            this.$emit('update:modelValue', this.modelValue - this.step);
            this.$emit('change', this.modelValue - this.step, this.name);
        },

        increase() {
            this.$emit('update:modelValue', this.modelValue + this.step);
            this.$emit('change', this.modelValue + this.step, this.name);
        },
    }
}
</script>

<style lang="scss">
.input-quantity {
    display: flex;

    &__decrease {
        margin-right: 3px;
    }

    &__increase {
        margin-left: 3px;
    }
}
</style>
