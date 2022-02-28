<template>
    <div class="expand" :class="{'expand__expanded': expanded}" @click="expand">
        <icon-dropdown :class="'expand__button'"/>
    </div>
</template>

<script>
import IconDropdown from "@/Components/Icons/IconDropdown";

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

<style lang="scss">
@use "sass:math";
@import "../variables";

$animation_time: 150ms !default;
$animation: cubic-bezier(0.24, 0.19, 0.28, 1.29) !default;
$base_size_unit: 35px !default;
$base_black_color: #1e1e1e !default;

.expand {
    display: inline-flex !important;
    justify-content: center;
    align-items: center;
    width: $base_size_unit;
    height: $base_size_unit;
    cursor: pointer;

    &__button {
        display: block;
        width: math.div($base_size_unit, 4);
        height: math.div($base_size_unit, 4);
        transition: transform $animation $animation_time;
        color: $base_black_color;
    }

    &:hover &__button {
        transform: scale(1.2);
    }

    &__expanded &__button {
        transform: rotate(-180deg);
    }

    &__expanded:hover &__button {
        transform: rotate(-180deg) scale(1.2);
    }
}
</style>
