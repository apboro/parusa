<template>
    <div class="application__menu-item"
         :class="['application__menu-item-'+level, hovered ? 'application__menu-item-hovered' : '']"
         @mouseenter="show"
         @mouseleave="hide"
    >
        <router-link v-if="route" class="application__menu-item-link"
                     :to="{name:route}"
        ><span @click="$emit('hide')">{{ title }}</span>
        </router-link>
        <span v-else class="application__menu-item-link"><span @click="$emit('hide')">{{ title }}</span></span>
        <div v-if="children" class="application__menu-submenu" :class="'application__menu-submenu-'+level">
            <layout-menu-item v-for="(item, key) in children"
                              :key="key"
                              :item="item"
                              :level="level+1"
                              @hide="hide"
            />
        </div>
    </div>
</template>

<script>
export default {
    name: "LayoutMenuItem",

    props: {
        item: Object,
        level: {type: Number, default: 0},
    },

    data: () => ({
        hovered: false,
    }),

    computed: {
        title() {
            return typeof this.item.title !== 'undefined' ? this.item.title : 'not set';
        },
        route() {
            return typeof this.item.route !== 'undefined' ? this.item.route : null;
        },
        children() {
            return typeof this.item.items !== 'undefined' && this.item.items.length > 0 ? this.item.items : null;
        },
    },

    methods: {
        show() {
            if (this.children) {
                this.hovered = true;
            }
        },
        hide() {
            this.hovered = false;
        },
    },
}
</script>
