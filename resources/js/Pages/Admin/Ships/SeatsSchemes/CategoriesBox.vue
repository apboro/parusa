<script>
import seatMethods from "@/Mixins/seatMethods.vue";

export default {
    name: "CategoriesBox",
    mixins: [seatMethods],
    props: {
        grades: Array,
        categories: Array,
        categoryColors: Object,
    }


}
</script>

<template>
    <div v-if="categories && categories.length > 0" class="ap-categories-container">
        <div v-for="category in categories" class="ap-category-box">
            <div class="ap-seats-box">
                <div class="ap-color-square" :style="{ backgroundColor: categoryColors[category.name] }"></div>
                <div> - {{ getCategoryName(category.id) }}</div>
            </div>
            <div class="ap-grades_box">
                <div v-for="gradeName in getGrades(category.id)"> - {{ gradeName }}</div>
            </div>
        </div>
    </div>
</template>

<style scoped lang="scss">
.ap-categories-container {
    display: flex;
    justify-content: space-around;
    align-items: flex-start;
}

.ap-category-box {
    display: flex;
    flex-direction: column;

}
.ap-seats-box {
    display: flex;
    justify-content: flex-start;
    align-items: center;
}

.ap-color-square {
    width: 20px;
    height: 20px;
    margin: 5px;
}

.ap-grades_box {
    display: flex;
    flex-direction: column;
}
</style>
