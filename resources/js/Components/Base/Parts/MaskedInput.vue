<template>
    <span class="w-100">
        <input
            style="position: absolute; opacity: 0; width: 0; height: 0; padding: 0; border: none; outline: none; margin: 0"
            v-model="proxyValue"
            :type="type"
            :required="required"
            :disabled="disabled"
            :autocomplete="autocomplete"
            :placeholder="placeholder"
            @keydown="updateDisplayCursorPosition"
            @keyup="updateDisplayCursorPosition"
            @blur="blur"
            ref="input">
        <span class="base-input__input" :class="classes" v-html="display" @mousedown="setCursor" ref="fake"></span>
    </span>
</template>

<script>
// https://codepen.io/neves/pen/BWjorq
import empty from "../../../Helpers/Lib/empty";

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

        type: {type: String, default: 'text'},
        autocomplete: {type: String, default: 'off'},
        placeholder: {type: String, default: null},

        mask: {type: String, default: null},
        maskHint: {type: String, default: null},

        classes: {type: [String, Array, Object], default: null},
    },

    emits: ['update:modelValue', 'changed'],

    computed: {
        isDirty() {
            return empty(this.innerOriginal) ? !empty(this.innerValue) : this.innerOriginal !== this.innerValue;
        },

        proxyValue: {
            get() {
                return this.innerValue;
            },
            set(value) {
                // first get value filtered with mask rules
                let newValue = this.filterValue(value);

                // remember current cursor position
                let cursor = this.$refs.input.selectionEnd;

                if (this.innerValue === newValue) {
                    // disabled symbol
                    this.$refs.input.value = this.innerValue;
                    this.$refs.input.setSelectionRange(cursor - 1, cursor - 1);
                    this.updateDisplayCursorPosition();
                    return;
                }

                this.innerValue = newValue;
                this.$refs.input.value = this.innerValue;
                if (value.length > this.maskCount) {
                    // enabled symbol with value overflow
                    this.$refs.input.setSelectionRange(cursor, cursor);
                }
                this.updateDisplayCursorPosition();

                // update model value if mask correctly filled
                if (this.innerValue.length === this.maskCount) {
                    this.update(this.applyMask(this.innerValue), this.isDirty);
                } else {
                    this.update(null, this.isDirty);
                }
            }
        },

        display() {
            if (!this.focused && empty(this.innerValue) && this.placeholder) {
                return '<span class="base-input__input-placeholder">' + this.placeholder + '</span>';
            }
            let out = '';
            for (let i = 0; i <= this.maskObject.length; i++) {
                if (i === this.displayCursorPosition) {
                    out += '<span class="base-input__input-mask-cursor"></span>';
                }
                if (i < this.maskObject.length) {
                    if (this.maskObject[i].editable) {
                        // insert value
                        if (empty(this.innerValue) || String(this.innerValue).length <= this.maskObject[i].inputCursor) {
                            if (this.maskHint !== null && this.maskHint[i]) {
                                // insert placeholder hint
                                out += '<span class="base-input__input-mask-placeholder" data-position="' + this.maskObject[i].inputCursor + '">' + this.maskHint[i] + '</span>';
                            } else {
                                // or generic placeholder
                                out += '<span class="base-input__input-mask-placeholder base-input__input-mask-placeholder-generic" data-position="' + this.maskObject[i].inputCursor + '">#</span>';
                            }
                        } else {
                            out += '<span class="base-input__input-mask-value" data-position="' + this.maskObject[i].inputCursor + '">' + String(this.innerValue)[this.maskObject[i].inputCursor] + '</span>';
                        }
                    } else {
                        // insert mask placeholder
                        out += '<span class="base-input__input-mask" data-position="' + this.maskObject[i].inputCursor + '">' + this.maskObject[i].char + '</span>';
                    }
                }
            }

            return out;
        },

        maskCount() {
            if (this.mask === null) {
                return 0;
            }
            let tokenSet = Object.keys(tokens);
            let count = 0;
            for (let i = 0; i < this.mask.length; i++) {
                if (tokenSet.indexOf(this.mask[i]) !== -1) {
                    count++;
                }
            }
            return count;
        },
    },

    data: () => ({
        innerOriginal: null,
        innerValue: null,
        maskObject: [],
        displayCursorPosition: null,
        focused: false,
    }),

    mounted() {
        this.initMask();
        this.innerValue = this.unMask(this.modelValue);
        this.innerOriginal = this.unMask(this.original);
    },

    watch: {
        mask() {
            this.initMask();
            this.innerValue = this.unMask(this.modelValue);
        },
        modelValue(value) {
            if (value !== null) {
                this.innerValue = this.unMask(value);
            }
        },
        original(value) {
            if (value !== null) {
                this.innerOriginal = this.unMask(value);
            }
        },
    },

    methods: {
        initMask() {
            let inputCursor = 0;
            let displayCursor = 0;
            let tokenSet = Object.keys(tokens);
            for (let i = 0; i < this.mask.length; i++) {
                if (tokenSet.indexOf(this.mask[i]) !== -1) {
                    // if token exists, current position is editable
                    this.maskObject.push({
                        editable: true,
                        token: this.mask[i],
                        inputCursor: inputCursor++,
                        displayCursor: displayCursor++,
                    });
                } else {
                    // otherwise, position is mask placeholder
                    this.maskObject.push({
                        editable: false,
                        char: this.mask[i],
                        inputCursor: inputCursor,
                        displayCursor: displayCursor++,
                    });
                }
            }
        },

        unMask(value) {
            if (empty(value) || this.mask === null) {
                return value;
            }
            let tokenSet = Object.keys(tokens);
            let unmasked = '';
            for (let i = 0; i < this.mask.length; i++) {
                if (tokenSet.indexOf(this.mask[i]) !== -1 && value[i]) {
                    unmasked += value[i];
                }
            }
            return unmasked;
        },

        filterValue(value) {
            if (empty(value) || this.mask === null) {
                return value;
            }
            let out = '';
            let maskIndex = 0;
            let index = 0;
            let tokenSet = Object.keys(tokens);
            while (maskIndex < this.mask.length && index < value.length) {
                if (tokenSet.indexOf(this.mask[maskIndex]) !== -1) {
                    const rule = tokens[this.mask[maskIndex]];
                    if (value[index] && rule.pattern.test(value[index])) {
                        out += rule.transform ? rule.transform(value[index]) : value[index];
                        maskIndex++;
                    }
                    index++;
                } else {
                    maskIndex++;
                }
            }
            return out;
        },

        update(value, dirty) {
            if (value !== null) {
                value = String(value).trim();
            }
            this.$emit('update:modelValue', value);
            this.$emit('changed', this.name, value, dirty);
        },


        applyMask(value) {
            if (empty(value) || this.mask === null) {
                return value;
            }
            let out = '';
            let maskIndex = 0;
            let index = 0;
            let tokenSet = Object.keys(tokens);
            while (maskIndex < this.mask.length && index < value.length) {
                if (tokenSet.indexOf(this.mask[maskIndex]) !== -1) {
                    out += value[index];
                    maskIndex++;
                    index++;
                } else {
                    out += this.mask[maskIndex];
                    maskIndex++;
                }
            }
            return out;
        },

        updateDisplayCursorPosition() {
            let cursor = this.$refs.input.selectionEnd;
            let displayCursor = null;
            let displayCursorLast = null;
            this.maskObject.map(item => {
                if (item.inputCursor === cursor) {
                    displayCursor = item.displayCursor;
                }
                displayCursorLast = item.displayCursor;
            });

            this.displayCursorPosition = displayCursor !== null ? displayCursor : displayCursorLast + 1;
        },

        setCursor(event) {
            event.preventDefault();
            event.stopPropagation();
            let position = Number(event.target.dataset.position);
            if (isNaN(position)) {
                position = this.maskCount;
            }
            this.$refs.input.focus();
            this.focused = true;
            this.$nextTick(() => {
                this.$refs.input.setSelectionRange(position, position);
                this.updateDisplayCursorPosition();
            });
        },

        blur() {
            this.displayCursorPosition = null;
            this.focused = false;
        }
    }
}
</script>
