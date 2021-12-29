<template>
    <div class="base-dropdown">
        <div class="base-dropdown__display" :class="{'base-dropdown__display-differs': isDirty}" @click="toggle">
            <span class="base-dropdown__display-value" :class="{'base-dropdown__display-value-placeholder': value === null}">{{ value }}</span>
            <span class="base-dropdown__display-icon" :class="{'base-dropdown__display-icon-dropped':dropped}"><icon-dropdown/></span>
        </div>

        <div class="base-dropdown__list" :class="{'base-dropdown__list-shown': dropped, 'base-dropdown__list-top': toTop}">
            <div class="base-dropdown__list-search" v-if="search">
                <base-icon-input v-model="terms" ref="search">
                    <icon-search/>
                </base-icon-input>
            </div>
            <scroll-box :mode="'vertical'" :scrollable-class="'base-dropdown__list-wrapper'">
                <span class="base-dropdown__list-item" v-if="hasNull" :class="{'base-dropdown__list-item-current' : modelValue === null}"
                      @click="value = null">{{ placeholder }}</span>
                <span class="base-dropdown__list-item" v-for="(val, key) in displayableOptions"
                      :class="{'base-dropdown__list-item-current' : isCurrent(val['key'])}"
                      :key="key" @click="value = val['key']" v-html="displayValue(val['value'])"></span>
            </scroll-box>
        </div>
    </div>
</template>

<script>
import IconDropdown from "../Icons/IconDropdown";
import empty from "../../Helpers/Lib/empty";
import ScrollBox from "../ScrollBox";
import BaseIconInput from "./BaseIconInput";
import IconSearch from "../Icons/IconSearch";

export default {
    props: {
        modelValue: {type: [Boolean, String, Number, Object], default: null},
        name: String,
        original: {type: [Boolean, String, Number, Object], default: null},

        placeholder: {type: String, default: null},
        hasNull: {type: Boolean, default: false},

        options: {type: Array, default: () => ([])},
        keyBy: {type: String, default: null},
        valueBy: {type: String, default: null},
        showDisabled: {type: Boolean, default: false},

        search: {type: Boolean, default: false},
        toTop: {type: Boolean, default: false},
    },

    emits: ['update:modelValue', 'changed', 'dropped'],

    components: {
        IconSearch,
        BaseIconInput,
        ScrollBox,
        IconDropdown,
    },

    data: () => ({
        dropped: false,
        searchTerms: null,
    }),

    computed: {
        displayableOptions() {
            let options = [];
            this.options.map((option, key) => {
                if (
                    typeof option === "object" && option !== null &&
                    this.keyBy !== null && this.valueBy !== null &&
                    typeof option[this.keyBy] !== "undefined" && typeof option[this.valueBy] !== "undefined"
                ) {
                    if (this.showDisabled || typeof option['enabled'] === "undefined" || Boolean(option['enabled']) === true) {
                        const value = option[this.valueBy];
                        if (this.search) {
                            if (empty(this.terms) || String(value).toLowerCase().search(this.terms.toLowerCase()) !== -1) {
                                options.push({key: key, value: value});
                            }
                        } else {
                            options.push({key: key, value: value});
                        }
                    }
                } else {
                    options.push({key: key, value: option});
                }
            });
            return options;
        },

        value: {
            get() {
                if (this.modelValue === null) {
                    return this.placeholder;
                }

                if (this.keyBy !== null && this.valueBy !== null && !empty(this.options)) {
                    let current = null;
                    this.options.some((option => {
                        if (option[this.keyBy] === this.modelValue) {
                            current = option[this.valueBy];
                            return true;
                        }
                        return false;
                    }))

                    return current !== null ? current : this.modelValue;
                }

                return this.modelValue;
            },
            set(key) {
                let value;

                if (key === null) {
                    value = null;
                } else {
                    value = this.options[key];
                    if (typeof value === "object" && value !== null &&
                        this.keyBy !== null && this.valueBy !== null &&
                        typeof value[this.keyBy] !== "undefined") {
                        value = value[this.keyBy];
                    }
                }

                this.$emit('update:modelValue', value);
                this.$emit('changed', this.name, value);
                this.close();
            }
        },

        isDirty() {
            return this.original !== this.modelValue;
        },

        terms: {
            get() {
                return this.searchTerms;
            },
            set(value) {
                this.searchTerms = value;
                this.updateHeight();
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
                this.dropped = true;
                this.$emit('dropped');
                this.updateHeight();
                if (this.search) {
                    this.$nextTick(() => {
                        this.$refs.search.focus();
                    })
                }
                setTimeout(() => {
                    window.addEventListener('click', this.close);
                }, 100);
            }
        },

        updateHeight() {
            this.$nextTick(() => {
                const el = this.$el.querySelector('.base-dropdown__list');
                el.style.height = null;
                const height = el.clientHeight + 1;
                el.style.height = height + 'px';
            });
        },

        close() {
            window.removeEventListener('click', this.close);
            this.dropped = false;
        },

        isCurrent(key) {
            if (empty(this.options) || empty(this.modelValue)) {
                return false;
            }
            const option = this.options[key];

            if (typeof option === "object" && option !== null && this.keyBy !== null && this.valueBy !== null &&
                typeof option[this.keyBy] !== "undefined" && typeof option[this.valueBy] !== "undefined"
            ) {
                return this.modelValue === option[this.keyBy];
            } else {
                return this.modelValue === option;
            }
        },

        displayValue(value) {
            if (this.search && this.terms) {
                value = this.$highlight(value, this.terms);
            }

            return value
        },
    },
}
</script>

