<template>
    <div v-if="seats && seats.length >= capacity">
        <svg viewBox="-20 70 500 200" xmlns="http://www.w3.org/2000/svg">
            <rect :style="selected(1) ? null : {'fill': getColor(seats[0])}"
                  @click="handleClick(seats[0])"
                  :class="{'rect_selected': selected(1)}"
                  x="66.461" y="95.07" width="21.127" height="22.007"/>
            <rect :style="selected(2) ? null : {'fill': getColor(seats[1])}"
                  @click="handleClick(seats[1])"
                  :class="{'rect_selected': selected(2)}"
                  x="66.461" y="132.042" width="22.007" height="22.007"/>
            <rect :style="selected(3) ? null : {'fill': getColor(seats[2])}" @click="handleClick(seats[2])"
                  :class="{'rect_selected': selected(3)}" x="65.589" y="169.894" width="23.768" height="20.246"/>
            <rect :style="selected(4) ? null : {'fill': getColor(seats[3])}" @click="handleClick(seats[3])"
                  :class="{'rect_selected': selected(4)}" x="174.736" y="94.19" width="25.528" height="20.246"/>
            <rect :style="selected(5) ? null : {'fill': getColor(seats[4])}" @click="handleClick(seats[4])"
                  :class="{'rect_selected': selected(5)}" x="172.975" y="129.401" width="27.289" height="21.127"/>
            <rect :style="selected(6) ? null : {'fill': getColor(seats[5])}" @click="handleClick(seats[5])"
                  :class="{'rect_selected': selected(6)}" x="256.602" y="93.31" width="29.049" height="22.007"/>
            <rect :style="selected(7) ? null : {'fill': getColor(seats[6])}" @click="handleClick(seats[6])"
                  :class="{'rect_selected': selected(7)}" x="254.842" y="126.761" width="31.69" height="23.768"/>
            <ellipse cx="228.433" cy="123.239" rx="15.845" ry="15.845" style="fill: #999999FF;"/>
            <rect :style="selected(8) ? null : {'fill': getColor(seats[7])}" @click="handleClick(seats[7])"
                  :class="{'rect_selected': selected(8)}" x="347.271" y="92.43" width="24.648" height="24.648"/>
            <rect :style="selected(9) ? null : {'fill': getColor(seats[8])}" @click="handleClick(seats[8])"
                  :class="{'rect_selected': selected(9)}" x="346.391" y="132.042" width="25.528" height="23.768"/>
            <rect :style="selected(10) ? null : {'fill': getColor(seats[9])}" @click="handleClick(seats[9])"
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
                    <div class="color-square" :style="{ backgroundColor: colors(category.id) }"></div>
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
import seatMethods from "@/Mixins/seatMethods.vue";

export default {
    name: 'Scheme3',
    components: {GuiContainer, GuiButton, DictionaryDropDown},
    mixins: [seatMethods],
    emits: ['selectSeat'],

    props: {
        capacity: Number,
        grades: Array,
        categories: Array,
        shipId: Number,
        seats: Array,
        editing: Boolean,
        selecting: Boolean,
    },

}
</script>


