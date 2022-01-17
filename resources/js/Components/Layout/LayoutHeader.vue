<template>
    <header class="application__header">
        <div class="application__header-wrapper">
            <div class="application__header-title">
                <router-link :to="{name:'home'}" :class="'application__header-title-link'">
                    <icon-logo :class="'application__header-title-link-icon'"/>
                </router-link>
                <span class="application__header-title-text">{{ user.organization }}</span>
            </div>
            <div class="application__header-menu">
                <slot name="menu"/>
            </div>
            <div class="application__header-personal">
                <slot name="personal"/>
            </div>
        </div>
    </header>
</template>

<script>
import IconLogo from "../Icons/IconLogo";

export default {
    components: {IconLogo},

    props: {
        user: {
            type: Object,
            default: () => ({
                name: null,
                organization: null,
                position: null,
                avatar: null,
            })
        },
    },
}
</script>
