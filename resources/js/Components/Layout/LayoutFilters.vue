<template>
    <div class="layout-filters" v-if="$slots.default || $slots.search">
        <div class="layout-filters__filters">
            <slot/>
        </div>
        <div class="layout-filters__search" v-if="$slots.search">
            <slot name="search"/>
        </div>
    </div>
</template>

<script>
export default {}
</script>

<style lang="scss">
.layout-filters {
    display: flex;
    flex-direction: row;
    box-sizing: border-box;
    padding: 20px 0;

    &__filters {
        display: flex;
        flex-direction: row;
        flex-grow: 1;

        & > * {
            margin-right: 20px;
            min-width: 150px;
        }
    }

    &__search {
        display: flex;
        flex-direction: row;
        flex-grow: 0;
        justify-content: right;

        & > * {
            max-width: 350px;
            flex-grow: 1;
        }
    }
}
</style>
