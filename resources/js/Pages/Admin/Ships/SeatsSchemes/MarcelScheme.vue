<template>
    <div v-if="seats && seats.length >= 10">
        <svg viewBox="-20 70 500 200" xmlns="http://www.w3.org/2000/svg">
            <rect :style="selected(1) ? null : {'fill': color(seats[0].category.id)}"
                  @click="handleClick(1)"
                  :class="{'rect_selected': selected(1)}"
                  x="66.461" y="95.07" width="21.127" height="22.007"/>
            <rect :style="selected(2) ? null : {'fill': color(seats[1].category.id)}"
                  @click="handleClick(2)"
                  :class="{'rect_selected': selected(2)}"
                  x="66.461" y="132.042" width="22.007" height="22.007"/>
            <rect :style="selected(3) ? null : {'fill': color(seats[2].category.id)}" @click="handleClick(3)"
                  :class="{'rect_selected': selected(3)}" x="65.589" y="169.894" width="23.768" height="20.246"/>
            <rect :style="selected(4) ? null : {'fill': color(seats[3].category.id)}" @click="handleClick(4)"
                  :class="{'rect_selected': selected(4)}" x="174.736" y="94.19" width="25.528" height="20.246"/>
            <rect :style="selected(5) ? null : {'fill': color(seats[4].category.id)}" @click="handleClick(5)"
                  :class="{'rect_selected': selected(5)}" x="172.975" y="129.401" width="27.289" height="21.127"/>
            <rect :style="selected(6) ? null : {'fill': color(seats[5].category.id)}" @click="handleClick(6)"
                  :class="{'rect_selected': selected(6)}" x="256.602" y="93.31" width="29.049" height="22.007"/>
            <rect :style="selected(7) ? null : {'fill': color(seats[6].category.id)}" @click="handleClick(7)"
                  :class="{'rect_selected': selected(7)}" x="254.842" y="126.761" width="31.69" height="23.768"/>
            <ellipse cx="228.433" cy="123.239" rx="15.845" ry="15.845" style="fill: #999999FF;"/>
            <rect :style="selected(8) ? null : {'fill': color(seats[7].category.id)}" @click="handleClick(8)"
                  :class="{'rect_selected': selected(8)}" x="347.271" y="92.43" width="24.648" height="24.648"/>
            <rect :style="selected(9) ? null : {'fill': color(seats[8].category.id)}" @click="handleClick(9)"
                  :class="{'rect_selected': selected(9)}" x="346.391" y="132.042" width="25.528" height="23.768"/>
            <rect :style="selected(10) ? null : {'fill': color(seats[9].category.id)}" @click="handleClick(10)"
                  :class="{'rect_selected': selected(10)}" x="347.271" y="169.014" width="24.648" height="21.127"/>
            <text
                style="user-select: none; pointer-events: none; white-space: pre; fill: rgb(51, 51, 51); font-family: Arial, sans-serif; font-size: 20.6px;"
                x="71.639" y="114.387">1
            </text>
            <text
                style="user-select: none; pointer-events: none; white-space: pre; fill: rgb(51, 51, 51); font-family: Arial, sans-serif; font-size: 20.6px;"
                x="71.049" y="149.764">2
            </text>
            <text
                style="user-select: none; pointer-events: none; white-space: pre; fill: rgb(51, 51, 51); font-family: Arial, sans-serif; font-size: 20.6px;"
                x="68.101" y="176.887" transform="matrix(1, 0, 0, 1, 3.537727, 9.433977)">3
                <tspan x="68.10099792480469" dy="1em">​</tspan>
                <tspan x="68.10099792480469" dy="1em">​</tspan>
            </text>
            <text
                style="user-select: none; pointer-events: none; white-space: pre; fill: rgb(51, 51, 51); font-family: Arial, sans-serif; font-size: 20.6px;"
                x="180.719" y="102.594" transform="matrix(1, 0, 0, 1, -0.589614, 7.665089)">4
                <tspan x="180.718994140625" dy="1em">​</tspan>
            </text>
            <text
                style="user-select: none; pointer-events: none; white-space: pre; fill: rgb(51, 51, 51); font-family: Arial, sans-serif; font-size: 20.6px;"
                x="173.644" y="142.099" transform="matrix(1, 0, 0, 1, 5.306613, 2.948113)">5
                <tspan x="173.6439971923828" dy="1em">​</tspan>
                <tspan x="173.6439971923828" dy="1em">​</tspan>
            </text>
            <text
                style="user-select: none; pointer-events: none; white-space: pre; fill: rgb(51, 51, 51); font-family: Arial, sans-serif; font-size: 20.6px;"
                x="259.729" y="102.005" transform="matrix(1, 0, 0, 1, 5.306613, 9.433964)">6
                <tspan x="259.72900390625" dy="1em">​</tspan>
            </text>
            <text
                style="user-select: none; pointer-events: none; white-space: pre; fill: rgb(51, 51, 51); font-family: Arial, sans-serif; font-size: 20.6px;"
                x="257.96" y="137.382" transform="matrix(1, 0, 0, 1, 6.48584, 8.254726)">7
                <tspan x="257.9599914550781" dy="1em">​</tspan>
            </text>
            <text
                style="user-select: none; pointer-events: none; white-space: pre; fill: rgb(51, 51, 51); font-family: Arial, sans-serif; font-size: 20.6px;"
                x="354.068" y="112.028">8
            </text>
            <text
                style="user-select: none; pointer-events: none; white-space: pre; fill: rgb(51, 51, 51); font-family: Arial, sans-serif; font-size: 20.6px;"
                x="353.478" y="150.354">9
            </text>
            <text
                style="user-select: none; pointer-events: none; white-space: pre; fill: rgb(51, 51, 51); font-family: Arial, sans-serif; font-size: 20.6px;"
                x="351.71" y="179.835" transform="matrix(0.66546, 0, 0, 0.692307, 118.25081, 57.783093)">10
            </text>
        </svg>

        <div v-if="categories && categories.length > 0" class="categories-container">
            <div v-for="category in categories" class="category-box">
                <div class="seats-box">
                    <div class="color-square" :style="{ backgroundColor: color(category.id) }"></div>
                    <div> - {{ getCategoryName(category.id) }}</div>
                </div>
                <div class="grades_box">
                    <div v-for="gradeName in getGrades(category.id)"> - {{ gradeName }}</div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import DictionaryDropDown from "@/Components/Inputs/DictionaryDropDown.vue";
import GuiButton from "@/Components/GUI/GuiButton.vue";
import GuiContainer from "@/Components/GUI/GuiContainer.vue";

export default {
    name: 'MarcelScheme',
    components: {GuiContainer, GuiButton, DictionaryDropDown},
    emits: ['update'],

    props: {
        grades: Array,
        categories: Array,
        shipId: Number,
        seats: Array,
        editing: Boolean,
        selecting: Boolean,
    },

    data: () => ({
        selectedSeats: [],
        seatCategory: null,
    }),

    methods: {
        getGrades(categoryId) {
            let sort = this.grades.filter(el => el.seat_category_id === categoryId)
            return sort.map(el => el.grade.name);
        },
        getCategoryName(categoryId) {
            return this.categories.find(el => el.id === categoryId).name;
        },
        color(categoryId) {
            let color = 'gray';
            switch (categoryId) {
                case 1:
                    color = "pink";
                    break;
                case 2:
                    color = "#8FCBF1FF";
                    break;
                case 3:
                    color = "#94F18FFF";
                    break;
            }

            return color;
        },
        handleClick(seat_number) {
            if (this.editing === false && this.selecting === false)
                return;
            if (this.selectedSeats.includes(seat_number)) {
                this.selectedSeats = this.selectedSeats.filter(seat => seat !== seat_number);
                this.$emit('update', {selectedSeats: this.selectedSeats})
                return;
            }
            this.selectedSeats.push(seat_number)
            this.$emit('update', {seatNumber: seat_number, selectedSeats: this.selectedSeats})
        },

        selected(seat_number) {
            return this.selectedSeats.includes(seat_number)
        },

    }
}
</script>

<style lang="scss" scoped>
rect {
    fill: #6acce4;
}

.rect_selected {
    fill: #FCD327FF;
}

.categories-container {
    display: flex;
    justify-content: space-around;
    align-items: flex-start;
}

.category-box {
    display: flex;
    flex-direction: column;

}
.seats-box {
    display: flex;
    justify-content: flex-start;
    align-items: center;
}

.color-square {
    width: 20px;
    height: 20px;
    margin: 5px;
}

.grades_box {
    display: flex;
    flex-direction: column;
}

</style>
