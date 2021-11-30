<template>
    <header class="application__header">
        <div class="application__header-title">
            <router-link :to="{name:'home'}">title</router-link>
        </div>
        <div class="application__header-menu">
            <slot name="menu"/>
        </div>
        <div class="application__header-personal">
            <slot name="personal"/>
        </div>
    </header>
</template>

<script>
export default {}
</script>
