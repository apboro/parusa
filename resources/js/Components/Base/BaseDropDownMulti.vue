<template>
    <div class="base-dropdown-multi">
        <div class="base-dropdown-multi__display" :class="{'base-dropdown-multi__display-differs': isDirty}"
             @click="toggle">

            <div class="base-dropdown-multi__display-value">
                <span class="base-dropdown-multi__display-value-item" v-for="(val, key) in selectedValues"
                      :key="key"
                >{{ val.value }}<span class="base-dropdown-multi__display-value-item-remove"
                                      @click.stop="removeValue(val.key)"></span></span>
            </div>
            <span class="base-dropdown-multi__display-icon"
                  :class="{'base-dropdown-multi__display-icon-dropped':dropped}"><icon-dropdown/></span>
        </div>

        <div class="base-dropdown-multi__list"
             :class="{'base-dropdown-multi__list-shown': dropped, 'base-dropdown-multi__list-top': toTop}"
        >
            <span class="base-dropdown-multi__list-item" v-for="(val, key) in notSelectedValues"
                  :key="key"
                  @click="addValue(val.key)"
            >{{ val.value }}</span>
        </div>
    </div>
</template>

<script>
import IconDropdown from "../Icons/IconDropdown";
import empty from "../../Helpers/Lib/empty";
import clone from "../../Helpers/Lib/clone";

export default {
    props: {
        modelValue: {type: Array, default: () => ([])},
        name: String,
        original: {type: Array, default: () => ([])},

        placeholder: {type: String, default: null},

        options: {type: Array, default: () => ([])},
        keyBy: {type: String, default: null},
        valueBy: {type: String, default: null},
        toTop: {type: Boolean, default: false},
    },

    emits: ['update:modelValue', 'changed', 'dropped'],

    components: {
        IconDropdown,
    },

    data: () => ({
        dropped: false,
    }),

    computed: {
        selectedValues() {
            let selected = [];
            if (empty(this.options) || empty(this.modelValue)) {
                return selected;
            }
            this.options.map((option, key) => {
                if (
                    typeof option === "object" && option !== null &&
                    this.keyBy !== null && this.valueBy !== null &&
                    typeof option[this.keyBy] !== "undefined" && typeof option[this.valueBy] !== "undefined"
                ) {
                    if (this.modelValue.indexOf(option[this.keyBy]) >= 0) {
                        selected.push({key: key, value: option[this.valueBy]});
                    }
                } else {
                    if (this.modelValue.indexOf(option) >= 0) {
                        selected.push({key: key, value: option});
                    }
                }
            });

            return selected;
        },

        notSelectedValues() {
            let not_selected = [];
            if (empty(this.options)) {
                return not_selected;
            }
            this.options.map((option, key) => {
                if (
                    typeof option === "object" && option !== null &&
                    this.keyBy !== null && this.valueBy !== null &&
                    typeof option[this.keyBy] !== "undefined" && typeof option[this.valueBy] !== "undefined"
                ) {
                    if (empty(this.modelValue) || this.modelValue.indexOf(option[this.keyBy]) === -1) {
                        not_selected.push({key: key, value: option[this.valueBy]});
                    }
                } else {
                    if (empty(this.modelValue) || this.modelValue.indexOf(option) === -1) {
                        not_selected.push({key: key, value: option});
                    }
                }
            });

            return not_selected;
        },

        isDirty() {
            if (empty(this.original) && empty(this.modelValue)) {
                return false;
            } else if (!empty(this.original) && !empty(this.modelValue)) {
                return JSON.stringify(this.original.sort()) !== JSON.stringify(this.modelValue.sort());
            } else {
                return true;
            }
        }
    },

    methods: {
        toggle() {
            if (this.dropped === true) {
                this.dropped = false;
                setTimeout(() => {
                    window.removeEventListener('click', this.close);
                }, 100);
            } else {
                if (empty(this.notSelectedValues)) {
                    return;
                }
                this.dropped = true;
                this.$emit('dropped');
                setTimeout(() => {
                    window.addEventListener('click', this.close);
                }, 100);
            }
        },

        close() {
            window.removeEventListener('click', this.close);
            this.dropped = false;
        },

        addValue(key) {
            let value = this.options[key];

            if (typeof value === "object" && value !== null &&
                this.keyBy !== null && this.valueBy !== null &&
                typeof value[this.keyBy] !== "undefined") {
                value = value[this.keyBy];
            }

            let modelValue = clone(this.modelValue);
            modelValue.push(value);

            this.$emit('update:modelValue', modelValue);
            this.$emit('changed', this.name, modelValue);
            this.close();
        },

        removeValue(key) {
            let value = this.options[key];

            if (typeof value === "object" && value !== null &&
                this.keyBy !== null && this.valueBy !== null &&
                typeof value[this.keyBy] !== "undefined") {
                value = value[this.keyBy];
            }

            let modelValue = clone(this.modelValue);
            const index = modelValue.indexOf(value);
            if (index !== -1) {
                modelValue.splice(index, 1);
            }

            this.$emit('update:modelValue', modelValue);
            this.$emit('changed', this.name, modelValue);
            this.close();
        },
    },
}
</script>

