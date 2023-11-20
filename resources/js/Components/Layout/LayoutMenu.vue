<template>
    <div class="application__menu-wrapper" :class="{'application__menu-wrapper-active': opened}">
        <div class="application__menu-burger" @click="burgerToggle()">
            <span></span>
        </div>

        <nav class="application__menu">
            <div class="application__header-title" style="display: none;">
                <a href="/" class="router-link-active router-link-exact-active application__header-title-link" aria-current="page">
                    <img src="/storage/images/excurr.svg" class="application__header-title-link-icon">
                </a>
            </div>

            <layout-menu-item v-for="(item, key) in menu"
                              :key="key"
                              :item="item"
            />
        </nav>
    </div>
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

    data: () => ({
        opened: false,
    }),

    methods: {
        burgerToggle() {
            this.opened = !this.opened
        }
    }
}
</script>

<style lang="scss">
    @media (max-width: 767px) {
        .application__menu-wrapper {
            .application__menu {
                flex-direction: column;
                height: auto;
                row-gap: 20px;
                padding-top: 20px;
                display: none;

                &-submenu-0 {
                    bottom: -5px;
                }

                &-burger {
                    transition: all 0.3s ease;

                    span {
                        width: 20px;
                        height: 2px;
                        display: inline-block;
                        background: #0B68C2;
                        position: relative;

                        &::before {
                            position: absolute;
                            content: '';
                            top: -7px;
                            left: 0;
                            width: 20px;
                            height: 2px;
                            display: inline-block;
                            background: #0B68C2;
                        }

                        &::after {
                            position: absolute;
                            content: '';
                            bottom: -7px;
                            left: 0;
                            width: 20px;
                            height: 2px;
                            display: inline-block;
                            background: #0B68C2;
                        }
                    }
                }
            }
        }

        .application__menu-wrapper-active {

            .application__menu-burger {
                span {
                    transform: rotate(135deg);

                    &::before {
                        transform: rotate(90deg);
                        top: 0;
                    }

                    &::after {
                        display: none;
                    }
                }
            }

            .application__menu {
                display: flex;
            }
        }
    }
</style>
