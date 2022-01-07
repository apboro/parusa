<template>
    <label class="base-input" :class="{'base-input__differs': is_dirty, 'base-input__not-valid': !valid}">
        <span class="base-input__icon base-input__icon-clickable" @click="toggle">
            <icon-calendar/>
        </span>
        <masked-input
            :classes="'base-input__input-no-left'"
            v-model="proxyValue"
            :name="name"
            :required="required"
            :disabled="disabled"
            :original="original"
            :type="type"
            :autocomplete="autocomplete"
            :placeholder="placeholder"
            :mask="'##.##.####'"
            :maskHint="'дд.мм.гггг'"
            @changed="changed"
            ref="input"
        />
        <date-picker :class="{'base-input__popup': 1, 'base-input__popup-shown': dropped}" :date="modelValue" @selected="selected"/>
    </label>
</template>

<script>
import MaskedInput from "./Parts/MaskedInput";
import IconCalendar from "../Icons/IconCalendar";
import DatePicker from "./Parts/DatePicker";

export default {
    components: {
        DatePicker,
        IconCalendar,
        MaskedInput
    },

    props: {
        modelValue: {type: String, default: null},
        name: String,
        original: {type: String, default: null},

        required: {type: Boolean, default: false},
        disabled: {type: Boolean, default: false},
        valid: {type: Boolean, default: true},

        type: {type: String, default: 'text'},
        autocomplete: {type: String, default: 'off'},
        placeholder: {type: String, default: null},
    },

    emits: ['update:modelValue', 'changed'],

    data: () => ({
        dropped: false,
        is_dirty: false,
    }),

    computed: {
        proxyValue: {
            get() {
                return this.modelValue;
            },
            set(value) {
                this.$emit('update:modelValue', value);
            }
        },
    },

    methods: {
        changed(name, value) {
            this.$emit('changed', this.name, value);
            this.is_dirty = this.$refs.input.isDirty;
        },

        selected(value) {
            this.proxyValue = value;
            this.$emit('changed', this.name, value);
            this.close();
            this.$nextTick(() => {
                this.is_dirty = this.$refs.input.isDirty;
            });
        },

        toggle() {
            if (this.dropped === true) {
                this.dropped = false;
                setTimeout(() => {
                    window.removeEventListener('click', this.close);
                }, 100);
            } else {
                this.dropped = true;
                setTimeout(() => {
                    window.addEventListener('click', this.close);
                }, 100);
            }
        },

        close() {
            window.removeEventListener('click', this.close);
            this.dropped = false;
        },
    }
}
</script>

