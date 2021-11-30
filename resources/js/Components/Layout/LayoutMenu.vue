<template>
    <nav class="application__menu">
        <layout-menu-item v-for="(item, key) in menu"
                          :key="key"
                          :item="item"
        />
    </nav>
</template>

<script>
import LayoutMenuItem from "./LayoutMenuItem";

export default {
    props: {
        menu: Array,
    },

    components: {
        LayoutMenuItem,
    }
}
</script>
