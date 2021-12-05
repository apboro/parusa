<template>
    <div class="actions-menu">
        <div class="actions-menu__button" @click="toggle" :class="{'actions-menu__button-active':dropped}">
            <span class="actions-menu__button-title" v-if="title">{{ title }}</span>
            <icon-bars/>
        </div>
        <div class="actions-menu__actions" :class="{'actions-menu__actions-shown': dropped}">
            <slot/>
        </div>
    </div>
</template>

<script>
import IconBars from "./Icons/IconBars";

export default {
    props: {
        title: {type: String, default: 'Действия'},
    },

    components: {IconBars},

    data: () => ({
        dropped: false,
    }),

    methods: {
        toggle() {
            if (this.dropped === true) {
                this.dropped = false;
                setTimeout(() => {
                    window.removeEventListener('click', this.close);
                }, 100);
            } else {
                this.dropped = true;
                this.$emit('dropped');
                setTimeout(() => {
                    window.addEventListener('click', this.close);
                }, 100);
            }
        },

        close() {
            window.removeEventListener('click', this.close);
            this.dropped = false;
        },
    }
}
</script>

