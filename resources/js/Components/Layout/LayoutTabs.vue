<template>
    <div class="tabs" v-if="tabs">
        <span class="tabs__tab" v-for="(tab, key) in tabs"
              :key="key"
              :class="{'tabs__tab-active': key === current}"
              @click="current = key"
        >{{ tab }}</span>
    </div>
</template>

<script>
export default {
    props: {
        tabs: {type: Object, default: null},
        initial: {type: String, default: null},
    },

    emits: ['change'],

    data: () => ({
        current_tab: null,
    }),

    computed: {
        current: {
            get() {
                return this.current_tab !== null ? this.current_tab : Object.keys(this.tabs)[0];
            },
            set(value) {
                if (this.current_tab === value) {
                    return;
                }
                this.current_tab = value;
                this.$emit('change', value);
            }
        }
    },

    created() {
        this.current_tab = (typeof this.initial !== "undefined" && this.initial !== null) ? this.initial : Object.keys(this.tabs)[0];
        this.$emit('change', this.current);
    }
}
</script>
