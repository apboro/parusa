<template>
    <div class="ap-checkout__gallery" v-if="count > 0">
        <img class="ap-checkout__gallery-image" :src="images[index]" :alt="alt"/>
        <div class="ap-checkout__gallery-navigation" v-if="count > 1">
            <div class="ap-checkout__gallery-navigation-previous" @click.stop.prevent="previous">
                <IconArrowRight/>
            </div>
            <div class="ap-checkout__gallery-navigation-next" @click.stop.prevent="next">
                <IconArrowRight/>
            </div>
        </div>
    </div>
</template>

<script>
import IconArrowRight from "@/Components/Icons/IconArrowRight";

export default {
    components: {IconArrowRight},
    props: {
        images: {type: Object, default: null},
        alt: {type: String, default: null},
    },

    computed: {
        count() {
            return (typeof this.images === "object" && this.images !== null) ? this.images.length : 0;
        }
    },

    data: () => ({
        index: 0,
    }),

    methods: {
        next() {
            this.index = (this.index > this.count - 2) ? 0 : this.index + 1;
        },
        previous() {
            this.index = (this.index === 0) ? this.count - 1 : this.index - 1;
        },
    }
}
</script>

<style lang="scss" scoped>
.ap-checkout__gallery {
    width: 100%;
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #7f7f7f;

    &-image {
        width: 100%;
        height: 100%;
        max-height: 50vh;
        object-fit: contain;
    }

    &-navigation {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;

        &-previous {
            position: absolute;
            top: 0;
            left: 0;
            width: 30%;
            height: 100%;
            cursor: pointer;
            opacity: 0.5;

            &:hover {
                opacity: 1;
            }

            & > svg {
                position: absolute;
                width: 50px;
                height: 50px;
                top: 50%;
                left: 10px;
                transform: translateY(-50%) rotate(180deg);
                color: #fff;
                filter: drop-shadow(0px 0px 1px rgba(0, 0, 0, 1));
            }
        }

        &-next {
            position: absolute;
            top: 0;
            right: 0;
            width: 70%;
            height: 100%;
            cursor: pointer;
            opacity: 0.5;

            &:hover {
                opacity: 1;
            }

            & > svg {
                position: absolute;
                width: 50px;
                height: 50px;
                top: 50%;
                right: 10px;
                transform: translateY(-50%);
                color: #fff;
                filter: drop-shadow(0px 0px 1px rgba(0, 0, 0, 1));
            }
        }
    }
}
</style>
