<template>
    <PopUp :title="title"
           :message="message"
           :buttons="popUpButtons"
           :align="align"
           :close-on-overlay="closeOnOverlay"
           :manual="true"
           :resolving="resolved"
           ref="popup"
    >
        <slot/>
    </PopUp>
</template>

<script>
import PopUp from "@/Components/PopUp";

export default {
    components: {PopUp},

    props: {
        title: {type: String, default: null},
        message: {type: String, default: null},
        buttons: {type: Array, default: () => ([{result: 'cancel', caption: 'Отмена'}])},
        saveButton: {type: String, default: 'save'},
        saveButtonCaption: {type: String, default: 'Применить'},
        align: {type: String, default: 'center'},
        manual: {type: Boolean, default: false},
        resolving: {type: Function, default: null},
        closeOnOverlay: {type: Boolean, default: false},
        form: {type: Object, default: null},
        options: {type: Object, default: () => ({})},
    },

    computed: {
        popUpButtons() {
            return [{result: this.saveButton, caption: this.saveButtonCaption, color: 'green'}, ...this.buttons];
        }
    },

    data: () => ({
        resolve_function: null,
    }),

    methods: {
        show() {
            this.$refs.popup.show();
            return new Promise(resolve => {
                this.resolve_function = resolve;
            });
        },

        hide() {
            return this.$refs.popup.hide();
        },

        resolved(result) {
            if (result !== this.saveButton) {
                this.$refs.popup.hide();
                return true;
            }
            if (!this.form.validate()) {
                return false;
            }
            this.$refs.popup.process(true);
            this.form.save(this.options)
                .then((values) => {
                    this.$refs.popup.hide();
                    this.resolve_function(values)
                    return true;
                })
                .finally(() => {
                    this.$refs.popup.process(false);
                });
            return false;
        }
    }
}
</script>
