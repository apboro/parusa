<template>
    <div>
        <component :is="getComponentName()"
                   :capacity="data.capacity"
                   :seats="data.seats"
                   :shipId="shipId"
                   :editing="editing"
                   :selecting="selecting"
                   :categories="data.categories"
                   :grades="data.seat_tickets_grades"
                   @selectSeat="handleSelectSeat" />
    </div>
</template>

<script>
import scheme12 from "@/Pages/Admin/Ships/SeatsSchemes/Scheme12.vue";
import scheme3 from "@/Pages/Admin/Ships/SeatsSchemes/Scheme3.vue";

export default {
    components: {
        scheme12: scheme12,
        scheme3: scheme3,
    },
    emits: ['selectSeat'],
    props: {
        data: {seats: Object, categories: Object, seat_tickets_grades: Object, capacity: Number},
        shipId: Number,
        editing: Boolean,
        selecting: Boolean,
    },
    methods: {
        getComponentName() {
            switch (this.shipId) {
                case 12:
                    return scheme12
                case 3:
                    return scheme3
            }

            return 'default-scheme';
        },
        handleSelectSeat(seat) {
            this.$emit('selectSeat', seat);
        },
    },


};
</script>

<style lang="scss">
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
