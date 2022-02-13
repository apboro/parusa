<template>
    <div class="input-datetime">
        <InputWrapper class="input-datetime__date" :dirty="isDateDirty" :disabled="disabled" :valid="valid" :has-focus="isFocused">
            <DateInput
                v-model="proxyDate"
                :from="from"
                :to="to"
                :placeholder="placeholder"
                :disabled="disabled"
                :small="small"
                :clearable="clearable"
                :pickOnClear="pickOnClear"
                @focus="isFocused = true"
                @blur="isFocused = false"
                ref="date"
            />
        </InputWrapper>
        <InputWrapper class="input-datetime__time" :dirty="isTimeDirty" :disabled="disabled" :valid="valid">
            <TimeInput
                v-model="proxyTime"
                :placeholder="null"
                :disabled="disabled"
                :small="small"
            />
        </InputWrapper>
    </div>
</template>

<script>
import InputWrapper from "@/Components/Inputs/Helpers/InputWrapper";
import DateInput from "@/Components/Inputs/Helpers/DateInput";
import TimeInput from "@/Components/Inputs/Helpers/TimeInput";

export default {
    components: {TimeInput, DateInput, InputWrapper},

    props: {
        modelValue: {type: String, default: null},
        from: {type: String, default: null},
        to: {type: String, default: null},
        original: {type: String, default: null},
        valid: {type: Boolean, default: true},
        disabled: {type: Boolean, default: false},
        placeholder: {type: String, default: null},
        small: {type: Boolean, default: false},
        clearable: {type: Boolean, default: false},
        pickOnClear: {type: Boolean, default: true},
    },

    emits: ['update:modelValue', 'change'],

    data: () => ({
        isFocused: false,
        date: null,
        time: null,
        internalUpdate: false,
    }),

    computed: {
        isDateDirty() {
            return this.original !== this.date;
        },
        isTimeDirty() {
            return this.original !== this.time;
        },
        proxyDate: {
            get() {
                return this.date;
            },
            set(value) {
                this.internalUpdate = true;
                this.date = value;
                this.set();
            }
        },
        proxyTime: {
            get() {
                return this.time;
            },
            set(value) {
                this.internalUpdate = true;
                this.time = value;
                this.set();
            }
        },
    },

    created() {
        this.init(this.modelValue);
    },

    watch: {
        modelValue(value) {
            if (!this.internalUpdate) {
                this.init(value);
            } else {
                this.internalUpdate = false;
            }
        }
    },

    methods: {
        init(value) {
            this.date = value;
            this.time = value;
        },
        set() {
            if (this.date === null || this.time === null) {
                return;
            }
            let value = new Date(this.time);
            let date = new Date(this.date);
            value.setFullYear(date.getFullYear());
            value.setMonth(date.getMonth());
            value.setDate(date.getDate());
            this.$emit('update:modelValue', value.toISOString());
            this.$emit('change', value.toISOString(), this.name);
        },
        addDays(value) {
            this.$refs.input.addDays(value);
        }
    }
}
</script>

<style lang="scss">
@import "../variables";

$base_size_unit: 35px !default;

.input-datetime {
    height: $base_size_unit;
    display: flex;
    width: 100%;

    &__date {
        width: 60%;
        margin-right: 5px;
    }

    &__time {
        flex-grow: 1;
        width: 40%;


    }
}
</style>
