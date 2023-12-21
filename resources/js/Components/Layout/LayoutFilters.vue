<template>
    <div class="layout-filters" v-if="$slots.default || $slots.search">
        <div class="layout-filters__title">
            <span>Фильтры</span> <span class="layout-filters__close" v-on:click="filtersClose()">&times;</span>
        </div>
        <div class="layout-filters__filters">
            <slot/>
            <button class="layout-filters__apply" type="button" v-on:click="filtersClose()">Применить</button>
        </div>

        <div class="layout-filters__search" v-if="$slots.search">
            <slot name="search"/>
        </div>
    </div>
</template>

<script>
export default {
    methods: {
        filtersClose() {
            let allFiltersVisible = document.getElementsByClassName("layout-filters");
            allFiltersVisible[0].style.visibility = 'hidden';
        },
    },
}
</script>

<style lang="scss">
.layout-filters {
    display: flex;
    box-sizing: border-box;
    padding: 20px 0;

    &__title {
        display: none;
    }

    &__filters {
        display: flex;
        flex-grow: 1;

        & > * {
            margin-right: 20px;
            min-width: 150px;
        }
    }

    &__search {
        display: flex;
        flex-grow: 0;
        justify-content: right;

        & > * {
            max-width: 350px;
            flex-grow: 1;
        }
    }

    &__apply {
        display: none;
    }
}

@media (max-width: 767px) {
    .layout-filters {
        flex-direction: column;
        row-gap: 20px;

        &__filters {
            flex-direction: column;
            row-gap: 20px;
        }

        &-item {
            margin-right: 0;
        }

        &__apply {
            display: block;
            margin-top: 20px;
            background: #0B68C2;
            color: #fff;
            padding: 10px 0;
            border: none;
            border-radius: 5px;
            font-family: -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica Neue, Arial, Noto Sans, sans-serif, Apple Color Emoji, Segoe UI Emoji, Segoe UI Symbol, Noto Color Emoji;
        }
    }

    .input-dropdown__list {
        width: 100% !important;
    }
}
</style>
