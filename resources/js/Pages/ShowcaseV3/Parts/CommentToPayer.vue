<script>
import ShowcaseIconSign from "@/Pages/Showcase/Icons/ShowcaseIconSign.vue";

export default {
    name: "CommentToPayer",
    components: {ShowcaseIconSign}
}
</script>

<template>
    <div class="ap-showcase__notice">
        <div class="ap-showcase__notice-text">
            При получении билетов в кассе необходимо предоставить документ, подтверждающий право на льготу:
            студенческий билет, пенсионное удостоверение и т.д.
        </div>
    </div>
</template>

<style scoped lang="scss">
    .ap-showcase__notice {
        width: 600px;

        &-text {
            color: #0E0E0E;
            font-family: Gilroy;
            font-size: 16px;
            font-weight: 400;
        }

        @media screen and (max-width: 769px) {
            width: 100%;
        }
    }
</style>
