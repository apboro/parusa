<template>
    <div class="base-table__container">
        <table class="base-table">
            <slot name="header"/>
            <tbody class="base-table__body">
            <slot/>
            </tbody>
        </table>
    </div>
</template>

<script>
export default {}
</script>
