<template>
    <div class="application__menu-item" v-if="accepted"
         :class="['application__menu-item-'+level, hovered ? 'application__menu-item-hovered' : '']"
         @mouseenter="show"
         @mouseleave="hide"
    >

        <router-link v-if="route" class="application__menu-item-link" :to="{name:route}" @click="menuToggle()">
            <span @click="$emit('hide')" :class="{'parent-span' : newNews && route === 'news-list'}">
                {{ title }}
                <icon-dropdown class="application__menu-item-link-drop" v-if="children"/>
                <span v-if="newNews && route === 'news-list'" class="newNews">{{newNews}}</span>
            </span>
        </router-link>

        <span v-else class="application__menu-item-no-link">
            <span @click="$emit('hide')">{{ title }}<icon-dropdown class="application__menu-item-link-drop" v-if="children"/></span>
        </span>

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
import IconDropdown from "../Icons/IconDropdown";
import roles from "@/Mixins/roles.vue";

export default {
    components: {IconDropdown},
    props: {
        item: Object,
        level: {type: Number, default: 0},
        newNews: Number
    },
    mixins: [roles],
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
        accepted() {
            return this.item['roles'] === undefined || this.hasRole(this.item['roles']);
        }
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
        menuToggle() {
            let burgerActive = document.getElementsByClassName("application__menu-burger");
            burgerActive[0].click();
        },
    },
}
</script>

<style scoped>
.parent-span {
    position: relative;
}

.newNews {
    position: absolute;
    bottom: 5px;
    right: -4px;
    width: 13px;
    height: 13px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    color: white;
    background-color: #00b2ff;
    border-radius: 50%;
}


</style>
