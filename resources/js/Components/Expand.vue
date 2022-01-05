<template>
    <div class="expand" :class="{'expand__expanded': expanded}" @click="expand">
        <icon-dropdown :class="'expand__button'"/>
    </div>
</template>

<script>
import IconDropdown from "./Icons/IconDropdown";

export default {
    emits: ['expand'],

    components: {
        IconDropdown,
    },

    data: () => ({
        expanded: false,
    }),

    methods: {
        expand() {
            this.expanded = !this.expanded;
            this.$emit('expand', this.expanded);
        }
    }
}
</script>
