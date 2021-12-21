<template>
    <label class="base-input" :class="{'base-input__differs': isDirty, 'base-input__not-valid': !valid}">
        <input
            class="base-input__input"
            v-model="maskedValue"
            :type="type"
            :required="required"
            :disabled="disabled"
            :autocomplete="autocomplete"
            :placeholder="mask"
            ref="input">
    </label>
</template>

<script>
// https://codepen.io/neves/pen/BWjorq
import empty from "../../Helpers/Lib/empty";

const tokens = {
    '#': {pattern: /\d/},
    'S': {pattern: /[a-zA-Z]/},
    'A': {pattern: /[0-9a-zA-Z]/},
    'U': {pattern: /[a-zA-Z]/, transform: v => v.toLocaleUpperCase()},
    'L': {pattern: /[a-zA-Z]/, transform: v => v.toLocaleLowerCase()}
}

export default {
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

        mask: {type: String, default: null},
    },

    emits: ['update:modelValue', 'changed'],

    computed: {
        isDirty() {
            return empty(this.original) ? !empty(this.modelValue) : this.original !== this.modelValue;
        },

        maskedValue: {
            get () {
                // fix removing mask character at the end.
                // Pressing backspace after 1.2.3 result in 1.2. instead of 1.2
                return this.modelValue === this.currentValue ? this.currentMask
                    : (this.currentMask = this.applyMask(this.modelValue, this.mask, true))
            },

            set (newValue) {
                let currentPosition = this.$refs.input.selectionEnd
                let lastMask = this.currentMask
                // update the input before restoring the cursor position
                this.$refs.input.value = this.currentMask = this.applyMask(newValue, this.mask)

                if (this.currentMask.length <= lastMask.length) { // BACKSPACE
                    // when chars are removed, the cursor position is already right
                    this.$refs.input.setSelectionRange(currentPosition, currentPosition)
                } else { // inserting characters
                    // if the substring till the cursor position is the same, don't change position
                    if (newValue.substring(0, currentPosition) == this.currentMask.substring(0, currentPosition)) {
                        this.$refs.input.setSelectionRange(currentPosition, currentPosition)
                    } else { // increment 1 fixed position, but will not work if the mask has 2+ placeholders, like: ##//##
                        this.$refs.input.setSelectionRange(currentPosition+1, currentPosition+1)
                    }
                }
                this.currentValue = this.applyMask(newValue, this.mask, this.masked)
                this.update(this.currentValue)
            }
        }
    },

    data: () => ({
        currentValue: '',
        currentMask: '',
    }),

    methods: {
        update(value) {
            value = String(value).trim();
            this.$emit('update:modelValue', value);
            this.$emit('changed', this.name, value);
        },

        applyMask(value, mask, masked = true) {
            value = value || ""
            let iMask = 0
            let iValue = 0
            let output = ''
            while (iMask < mask.length && iValue < value.length) {
                let cMask = mask[iMask]
                let masker = tokens[cMask]
                let cValue = value[iValue]
                if (masker) {
                    if (masker.pattern.test(cValue)) {
                        output += masker.transform ? masker.transform(cValue) : cValue
                        iMask++
                    }
                    iValue++
                } else {
                    if (masked) output += cMask
                    if (cValue === cMask) iValue++
                    iMask++
                }
            }
            return output
        },
    }
}
</script>

