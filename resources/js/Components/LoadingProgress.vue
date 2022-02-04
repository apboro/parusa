<template>
    <div class="loading-progress__container">
        <div v-if="loading" class="loading-progress__wrapper" :class="'loading-progress__wrapper-' + opacity">
            <div class="loading-progress"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>
        </div>
        <slot></slot>
    </div>
</template>

<script>
export default {
    props: {
        loading: {type: Boolean, default: false},
        opacity: {type: Number, default: 50}
    }
}
</script>

<style lang="scss">
@use "sass:math";
@import "variables";

$base_white_color: #ffffff !default;
$base_primary_color: #1660ad !default;

.loading-progress__container {
    width: 100%;
    height: 100%;
    position: relative;
    min-height: 80px;
}

.loading-progress__wrapper {
    position: absolute;
    z-index: 99;
    width: 100%;
    height: 100%;
    top: 0;
    left: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    background-color: $base_white_color;

    @for $i from 0 through 10 {
        &-#{$i * 10} {
            background-color: transparentize($base_white_color, 1 - math.div($i, 10));
        }
    }
}

.loading-progress {
    display: inline-block;
    position: relative;
    width: 80px;
    height: 80px;
}

.loading-progress div {
    animation: loading-progress 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
    transform-origin: 40px 40px;
}

.loading-progress div:after {
    content: " ";
    display: block;
    position: absolute;
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: $base_primary_color;
    margin: -4px 0 0 -4px;
}

.loading-progress div:nth-child(1) {
    animation-delay: -0.036s;
}

.loading-progress div:nth-child(1):after {
    top: 63px;
    left: 63px;
}

.loading-progress div:nth-child(2) {
    animation-delay: -0.072s;
}

.loading-progress div:nth-child(2):after {
    top: 68px;
    left: 56px;
}

.loading-progress div:nth-child(3) {
    animation-delay: -0.108s;
}

.loading-progress div:nth-child(3):after {
    top: 71px;
    left: 48px;
}

.loading-progress div:nth-child(4) {
    animation-delay: -0.144s;
}

.loading-progress div:nth-child(4):after {
    top: 72px;
    left: 40px;
}

.loading-progress div:nth-child(5) {
    animation-delay: -0.18s;
}

.loading-progress div:nth-child(5):after {
    top: 71px;
    left: 32px;
}

.loading-progress div:nth-child(6) {
    animation-delay: -0.216s;
}

.loading-progress div:nth-child(6):after {
    top: 68px;
    left: 24px;
}

.loading-progress div:nth-child(7) {
    animation-delay: -0.252s;
}

.loading-progress div:nth-child(7):after {
    top: 63px;
    left: 17px;
}

.loading-progress div:nth-child(8) {
    animation-delay: -0.288s;
}

.loading-progress div:nth-child(8):after {
    top: 56px;
    left: 12px;
}

@keyframes loading-progress {
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(360deg);
    }
}
</style>
