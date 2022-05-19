<template>
    <CheckoutPopUp :title="title" ref="popup">
        <div class="ap-checkout__text">
            <div class="ap-checkout__text-image" v-if="info.data['images'][0]">
                <CheckoutGallery :images="[info.data['images'][0]]" :alt="info.data['name']"/>
            </div>
            <div class="ap-checkout__text-paragraph" v-for="paragraph in description">{{ paragraph }}</div>
            <CheckoutGallery class="ap-checkout__gallery_pt" v-if="info.data['map_images'] && info.data['map_images'][0]" :images="[info.data['map_images'][0]]" :alt="info.data['name']"/>
        </div>
    </CheckoutPopUp>
</template>

<script>
import data from "@/Core/Data";
import CheckoutPopUp from "@/Pages/Checkout/Components/CheckoutPopUp";
import IconArrowRight from "@/Components/Icons/IconArrowRight";
import CheckoutGallery from "@/Pages/Checkout/Components/CheckoutGallery";

export default {
    props: {
        crm_url: {type: String, default: 'https://cp.parus-a.ru'},
        debug: {type: Boolean, default: false},
    },

    components: {
        CheckoutGallery,
        IconArrowRight,
        CheckoutPopUp,
    },

    computed: {
        title() {
            return 'Экскурсия — ' + this.info.data['name'];
        },
        description() {
            return typeof this.info.data['description'] !== "undefined" && this.info.data['description'] !== null ? this.info.data['description'].split('\n') : [];
        },
    },

    data: () => ({
        info: data('/showcase/excursion'),
    }),

    methods: {
        show(id) {
            this.info.url = this.url('/showcase/excursion');
            this.info.reset();
            this.$refs.popup.show();
            this.$refs.popup.process(true);
            this.info.load({id: id})
                .finally(() => {
                    this.$refs.popup.process(false);
                })
        },
        url(path) {
            return this.crm_url + path + (this.debug ? '?XDEBUG_SESSION_START=PHPSTORM' : '');
        },
    }
}
</script>

<style lang="scss" scoped>
@import "../variables";

.ap-checkout__text {
    font-family: $checkout_font;
    color: $checkout_text_color;
    font-size: 16px;
    margin: 0 0 10px 0;
    line-height: 22px;

    &-image {
        float: left;
        width: 400px;
        margin: 0 30px 15px 0;
    }

    &-paragraph {
        margin-bottom: 10px;
    }
}

.ap-checkout__gallery_pt {
    margin-top: 30px;
}

@media screen and (max-width: 800px) {
    .ap-checkout__text {
        &-image {
            width: 100%;
        }
    }
}
</style>
