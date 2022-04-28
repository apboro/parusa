<template>
    <ShowcasePopUp :title="title" ref="popup">
        <div class="ap-showcase__text">
            <div class="ap-showcase__text-image" v-if="info.data['images'][0]">
                <ShowcaseGallery :images="[info.data['images'][0]]" :alt="info.data['name']"/>
            </div>
            <div class="ap-showcase__text-paragraph" v-for="paragraph in description">{{ paragraph }}</div>
        </div>
    </ShowcasePopUp>
</template>

<script>
import data from "@/Core/Data";
import ShowcasePopUp from "@/Pages/Showcase/Components/ShowcasePopUp";
import IconArrowRight from "@/Components/Icons/IconArrowRight";
import ShowcaseGallery from "@/Pages/Showcase/Components/ShowcaseGallery";

export default {
    components: {
        ShowcaseGallery,
        IconArrowRight,
        ShowcasePopUp,
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
            this.info.reset();
            this.$refs.popup.show();
            this.$refs.popup.process(true);
            this.info.load({id: id})
                .finally(() => {
                    this.$refs.popup.process(false);
                })
        }
    }
}
</script>

<style lang="scss" scoped>
$showcase_font: Gilroy;
$showcase_text_color: #2e2e2e !default;

.ap-showcase__text {
    font-family: $showcase_font;
    color: $showcase_text_color;
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

@media screen and (max-width: 800px) {
    .ap-showcase__text {
        &-image {
            width: 100%;
        }
    }
}
</style>
