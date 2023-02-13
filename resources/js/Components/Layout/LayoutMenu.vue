<template>
    <div class="application__menu-burger" @click="burgerToggle()">
        <span></span>
    </div>

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
    },

    methods: {
        burgerToggle() {
            let burgerMenu = document.querySelector('.application__header-menu');
            if ( burgerMenu.classList.contains('active') ) {
                burgerMenu.classList.remove('active')
            } else {
                burgerMenu.classList.add('active')
            }
        }
    }
}
</script>
