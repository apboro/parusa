<template>
    <div>
        <component :is="getComponentName()"
                   :tripId="data.id"
                   :capacity="data.capacity"
                   :shipId="shipId"
                   :editing="editing"
                   :selecting="selecting"
                   :categories="data.categories"
                   :grades="data.seat_tickets_grades"
                   @selectSeat="handleSelectSeat"/>
    </div>
</template>

<script>
import scheme12 from "@/Pages/Admin/Ships/SeatsSchemes/Scheme12.vue";
import scheme3 from "@/Pages/Admin/Ships/SeatsSchemes/Scheme3.vue";
import scheme58 from "@/Pages/Admin/Ships/SeatsSchemes/Scheme58.vue";
import scheme132 from "@/Pages/Admin/Ships/SeatsSchemes/Scheme132.vue";
import LoadingProgress from "@/Components/LoadingProgress.vue";

export default {
    components: {
        scheme12: scheme12,
        scheme3: scheme3,
        scheme58: scheme58,
        scheme132: scheme132,
    },
    emits: ['selectSeat'],
    props: {
        data: Object,
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
                case 58:
                    return scheme58
                case 132:
                    return scheme132
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
.ap-selected {
    fill: orange;
}

.ap-occupied {
    fill: #afacac;
}
</style>
