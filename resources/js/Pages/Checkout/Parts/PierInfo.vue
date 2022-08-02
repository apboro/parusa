<template>
    <CheckoutPopUp :title="title" ref="popup">
        <div class="ap-checkout__image-box">
            <div class="ap-checkout__image-box-image" v-if="info.data['images'][0]">
                <CheckoutGallery :images="[info.data['images'][0]]" :alt="info.data['name']"/>
            </div>
            <div class="ap-checkout__image-box-text">
                <div class="ap-checkout__text-paragraph ap-checkout__text-paragraph-icon">
                    <CheckoutIconPlace class="ap-checkout__text-icon"/>
                    {{ info.data['address'] }}
                </div>
                <div class="ap-checkout__text-paragraph ap-checkout__text-paragraph-icon">
                    <CheckoutIconClock class="ap-checkout__text-icon"/>
                    <div class="ap-checkout__text" v-for="paragraph in work_time">{{ paragraph }}</div>
                </div>
                <div class="ap-checkout__text-paragraph ap-checkout__text-paragraph-icon">
                    <CheckoutIconPhone class="ap-checkout__text-icon"/>
                    {{ info.data['phone'] }}
                </div>
            </div>
        </div>

        <div class="ap-checkout__title">Как добраться?</div>

        <div class="ap-checkout__text">
            <div class="ap-checkout__text-paragraph" v-for="paragraph in way_to">{{ paragraph }}</div>
        </div>

        <CheckoutGallery class="ap-checkout__gallery_pt" v-if="info.data['map_images'] && info.data['map_images'][0]" :images="[info.data['map_images'][0]]" :alt="info.data['name']"/>

    </CheckoutPopUp>
</template>

<script>
import data from "@/Core/Data";
import CheckoutPopUp from "@/Pages/Checkout/Components/CheckoutPopUp";
import CheckoutGallery from "@/Pages/Checkout/Components/CheckoutGallery";
import CheckoutIconPlace from "@/Pages/Checkout/Icons/CheckoutIconPlace";
import CheckoutIconClock from "@/Pages/Checkout/Icons/CheckoutIconClock";
import CheckoutIconPhone from "@/Pages/Checkout/Icons/CheckoutIconPhone";

export default {
    props: {
        crm_url: {type: String, default: 'https://cp.parus-a.ru'},
        debug: {type: Boolean, default: false},
    },

    components: {
        CheckoutIconPhone,
        CheckoutIconClock,
        CheckoutIconPlace,
        CheckoutGallery,
        CheckoutPopUp,
    },

    computed: {
        title() {
            return 'Причал — ' + this.info.data['name'];
        },
        work_time() {
            return typeof this.info.data['work_time'] !== "undefined" && this.info.data['work_time'] !== null ? this.info.data['work_time'].split('\n') : [];
        },
        way_to() {
            return typeof this.info.data['way_to'] !== "undefined" && this.info.data['way_to'] !== null ? this.info.data['way_to'].split('\n') : [];
        },
    },

    data: () => ({
        info: data('/checkout/pier'),
    }),

    methods: {
        show(id) {
            this.info.url = this.url('/checkout/pier');
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

.ap-checkout__title {
    font-family: $checkout_font;
    margin: 20px 0;
    font-size: 20px;
    color: $checkout_link_color;
    font-weight: bold;
    clear: both;
}

.ap-checkout__image-box {
    display: flex;

    &-image {
        width: 400px;
        flex-shrink: 0;
        flex-grow: 0;
    }

    &-text {
        box-sizing: border-box;
        padding: 20px 0 0 20px;
        font-family: $checkout_font;
        color: $checkout_text_color;
        font-size: 16px;
        margin: 0 0 10px 0;
    }
}

.ap-checkout__text {
    font-family: $checkout_font;
    color: $checkout_text_color;
    font-size: 16px;
    margin: 0 0 10px 0;
    clear: both;

    &-image {
        float: left;
        width: 400px;
        margin: 0 15px 15px 0;
    }

    &-paragraph {
        margin-bottom: 10px;

        &-icon {
            padding-left: 40px;
            position: relative;
            margin-bottom: 20px;
        }
    }

    &-icon {
        width: 26px;
        height: 26px;
        display: inline;
        vertical-align: bottom;
        margin-right: 10px;
        position: absolute;
        top: -3px;
        left: 0;
    }
}

.ap-checkout__gallery_pt {
    margin-top: 30px;
}

@media screen and (max-width: 800px) {
    .ap-checkout__image-box {
        flex-direction: column;
    }
    .ap-checkout__image-box-image {
        width: 100%;
    }
    .ap-checkout__image-box-text {
        width: 100%;
        padding: 20px 0 0 0;
    }
}
</style>
