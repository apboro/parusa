<template>
    <ShowcasePopUp :title="title" ref="popup">
        <div class="ap-showcase__image-box">
            <div class="ap-showcase__image-box-image" v-if="info.data['images'][0]">
                <ShowcaseGallery :images="[info.data['images'][0]]" :alt="info.data['name']"/>
            </div>
            <div class="ap-showcase__image-box-text">
                <div class="ap-showcase__text-paragraph ap-showcase__text-paragraph-icon">
                    <ShowcaseIconPlace class="ap-showcase__text-icon"/>
                    {{ info.data['address'] }}
                </div>
                <div class="ap-showcase__text-paragraph ap-showcase__text-paragraph-icon">
                    <ShowcaseIconClock class="ap-showcase__text-icon"/>
                    <div class="ap-showcase__text" v-for="paragraph in work_time">{{ paragraph }}</div>
                </div>
                <div class="ap-showcase__text-paragraph ap-showcase__text-paragraph-icon">
                    <ShowcaseIconPhone class="ap-showcase__text-icon"/>
                    {{ info.data['phone'] }}
                </div>
            </div>
        </div>

        <div class="ap-showcase__title">Как добраться?</div>

        <div class="ap-showcase__text">
            <div class="ap-showcase__text-paragraph" v-for="paragraph in way_to">{{ paragraph }}</div>
        </div>

        <ShowcaseGallery class="ap-showcase__gallery_pt" v-if="info.data['map_images'] && info.data['map_images'][0]" :images="[info.data['map_images'][0]]"
                         :alt="info.data['name']"/>

    </ShowcasePopUp>
</template>

<script>
import data from "@/Core/Data";
import PopUp from "@/Components/PopUp";
import ShowcasePopUp from "@/Pages/Showcase/Components/ShowcasePopUp";
import ShowcaseGallery from "@/Pages/Showcase/Components/ShowcaseGallery";
import ShowcaseIconPlace from "@/Pages/Showcase/Icons/ShowcaseIconPlace";
import ShowcaseIconClock from "@/Pages/Showcase/Icons/ShowcaseIconClock";
import ShowcaseIconPhone from "@/Pages/Showcase/Icons/ShowcaseIconPhone";

export default {
    props: {
        crm_url: {type: String, default: 'https://cp.parus-a.ru'},
        debug: {type: Boolean, default: false},
        session: {type: String, default: null},
    },

    components: {
        ShowcaseIconPhone,
        ShowcaseIconClock,
        ShowcaseIconPlace,
        ShowcaseGallery,
        ShowcasePopUp,
        PopUp,
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
        info: data('/showcase/pier'),
    }),

    methods: {
        show(id) {
            this.info.url = this.url('/showcase/pier');
            this.info.reset();
            this.$refs.popup.show();
            this.$refs.popup.process(true);
            this.info.load({id: id}, {'X-Ap-External-Session': this.session})
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

.ap-showcase__title {
    font-family: $showcase_font;
    margin: 20px 0;
    font-size: 20px;
    color: $showcase_link_color;
    font-weight: bold;
    clear: both;
}

.ap-showcase__image-box {
    display: flex;

    &-image {
        width: 400px;
        flex-shrink: 0;
        flex-grow: 0;
    }

    &-text {
        box-sizing: border-box;
        padding: 20px 0 0 20px;
        font-family: $showcase_font;
        color: $showcase_text_color;
        font-size: 16px;
        margin: 0 0 10px 0;
    }
}

.ap-showcase__text {
    font-family: $showcase_font;
    color: $showcase_text_color;
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

.ap-showcase__gallery_pt {
    margin-top: 30px;
}

@media screen and (max-width: 800px) {
    .ap-showcase__image-box {
        flex-direction: column;
    }
    .ap-showcase__image-box-image {
        width: 100%;
    }
    .ap-showcase__image-box-text {
        width: 100%;
        padding: 20px 0 0 0;
    }
}
</style>
