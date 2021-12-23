<template>
    <div class="dialogs__overlay" v-if="shown"
         :class="{
            'dialogs__overlay-hide': hiding,
            'dialogs__overlay-shown': showing,
         }" @click="resolve(null)"
    >
        <div class="dialogs__dialog" @click.stop.prevent="false">
            <loading-progress :loading="processing" :opacity="70">
                <div class="dialogs__dialog-wrapper">
                    <div class="dialogs__dialog-message" v-if="title">
                        <div class="dialogs__dialog-message-text">{{ title }}</div>
                    </div>
                    <slot/>
                    <div class="dialogs__dialog-buttons" :class="'dialogs__dialog-buttons-' + align">
                <span v-for="button in buttons"
                      class="button"
                      :class="' button__' + button.color"
                      @click="resolve(button.result)"
                >{{ button.caption }}</span>
                    </div>
                </div>
            </loading-progress>
        </div>
    </div>
</template>

<script>
import LoadingProgress from "./LoadingProgress";

export default {
    components: {LoadingProgress},
    props: {
        buttons: {
            type: Array,
            default: () => ([{result: 'ok', caption: 'OK', color: 'white'}])
        },
        align: {type: String, default: 'center'},
        manual: {type: Boolean, default: false},
        title: {type: String, default: null},
        resolving: {type: Function, default: null},
    },

    data: () => ({
        shown: false,
        promise: null,
        resolve_function: null,
        showing: false,
        hiding: false,
        processing: false,
    }),

    methods: {
        show() {
            this.processing = false;
            this.showing = true;
            this.hiding = false;
            this.shown = true;
            setTimeout(() => {
                this.showing = true;
            }, 100);
            return new Promise(resolve => {
                this.resolve_function = resolve;
            });
        },

        hide() {
            this.hiding = true;
            setTimeout(() => {
                this.shown = false;
                this.showing = false;
                this.hiding = false;
                this.processing = false;
            }, 300);
        },

        resolve(value) {
            if (this.resolving === null || (this.resolving(value) !== false)) {
                if (typeof this.resolve_function === "function") {
                    this.resolve_function(value);
                    if (!this.manual) {
                        this.hide();
                    }
                }
            }
        },

        process(value) {
            this.processing = value;
        },
    }
}
</script>
