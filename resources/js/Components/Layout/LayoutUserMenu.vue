<template>
    <div class="application__user-menu" @click="toggle">
        <div class="application__user-menu-icon">
            <icon-user v-if="!user.avatar"/>
        </div>
        <div class="application__user-menu-info">
            <span class="application__user-menu-info-name">{{ user.name }}</span>
            <span class="application__user-menu-info-org">{{ user.organization }}</span>
        </div>
        <icon-dropdown class="application__user-menu-drop" :class="{'application__user-menu-drop-dropped': show_menu}"/>
        <div class="application__user-menu-actions" :class="{'application__user-menu-actions-shown': show_menu}">
            <slot v-if="$slots.default"/>
        </div>
    </div>
</template>

<script>
import IconUser from "../Icons/IconUser";
import IconDropdown from "../Icons/IconDropdown";

export default {
    components: {IconDropdown, IconUser},
    props: {
        user: {
            type: Object,
            default: () => ({
                name: 'Администратор',
                organization: 'Алые Паруса',
                avatar: null,
            })
        },
    },
    data: () => ({
        show_menu: false,
    }),
    methods: {
        toggle() {
            if (this.show_menu === true) {
                this.show_menu = false;
                setTimeout(() => {
                    window.removeEventListener('click', this.close);
                }, 100);
            } else {
                this.show_menu = true;
                setTimeout(() => {
                    window.addEventListener('click', this.close);
                }, 100);
            }
        },

        close() {
            window.removeEventListener('click', this.close);
            this.show_menu = false;
        },
    }
}
</script>
