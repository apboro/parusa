<template>
    <div class="base-input base-input__days" :class="{'base-input__differs': isDirty, 'base-input__not-valid': !valid}">
        <label class="base-input__days-label"><input class="base-input__days-check" type="checkbox" value="1" v-model="proxy">Пн</label>
        <label class="base-input__days-label"><input class="base-input__days-check" type="checkbox" value="2" v-model="proxy">Вт</label>
        <label class="base-input__days-label"><input class="base-input__days-check" type="checkbox" value="3" v-model="proxy">Ср</label>
        <label class="base-input__days-label"><input class="base-input__days-check" type="checkbox" value="4" v-model="proxy">Чт</label>
        <label class="base-input__days-label"><input class="base-input__days-check" type="checkbox" value="5" v-model="proxy">Пт</label>
        <label class="base-input__days-label"><input class="base-input__days-check" type="checkbox" value="6" v-model="proxy">Сб</label>
        <label class="base-input__days-label"><input class="base-input__days-check" type="checkbox" value="7" v-model="proxy">Вс</label>
    </div>
</template>

<script>
import empty from "@/Core/Helpers/Empty";

export default {
    props: {
        modelValue: {type: Array, default: () => ([])},
        name: String,
        original: {type: Array, default: () => ([])},

        required: {type: Boolean, default: false},
        disabled: {type: Boolean, default: false},
        valid: {type: Boolean, default: true},
    },

    emits: ['update:modelValue', 'changed'],

    computed: {
        isDirty() {
            return empty(this.original) ? !empty(this.modelValue) : this.original !== this.modelValue;
        },

        proxy: {
            get() {
                return this.modelValue;
            },
            set(value) {
                this.update(value);
            }
        }
    },

    methods: {
        update(value) {
            this.$emit('update:modelValue', value);
            this.$emit('changed', this.name, value);
        },
    }
}
</script>

