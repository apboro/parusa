<template>
    <loading-progress :loading="data.loading">
        <div v-if="data.seats">
            <component :is="getComponentName()"
                       :tripId="data.id"
                       :shipId="shipId"
                       :seats="data.seats"
                       :editing="editing"
                       :selecting="selecting"
                       :categories="data.categories"
                       :grades="data.seat_tickets_grades"
                       @selectSeat="handleSelectSeat"/>
        </div>
    </loading-progress>
</template>

<script>
import scheme12 from "@/Pages/Admin/Ships/SeatsSchemes/Scheme12.vue";
import scheme3 from "@/Pages/Admin/Ships/SeatsSchemes/Scheme3.vue";
import scheme58 from "@/Pages/Admin/Ships/SeatsSchemes/Scheme58.vue";
import scheme132 from "@/Pages/Admin/Ships/SeatsSchemes/Scheme132.vue";
import scheme138 from "@/Pages/Admin/Ships/SeatsSchemes/Scheme138.vue";
import scheme139 from "@/Pages/Admin/Ships/SeatsSchemes/Scheme139.vue";
import LoadingProgress from "@/Components/LoadingProgress.vue";

export default {
    components: {
        LoadingProgress,
        scheme12: scheme12,
        scheme3: scheme3,
        scheme58: scheme58,
        scheme132: scheme132,
        scheme138: scheme138,
        scheme139: scheme139,
    },
    emits: ['selectSeat'],
    props: {
        data: Object,
        shipId: Number,
        editing: Boolean,
        selecting: Boolean,
    },

    beforeCreate() {
        if (this.data.loading) {
            axios.post("/api/trip/seats", {tripId: this.data.id})
                .then(response => this.data.seats = response.data.data.seats)
                .catch(error => {
                    this.$toast.error(error.response.data.message, 5000);
                })
                .finally(() => {
                    this.data.loading = false;
                })
        }
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
                case 138:
                    return scheme138
                case 139:
                    return scheme139
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
.seat_number{pointer-events: none}
.class_standard{fill: #ACD08F;}
.class_vip5{fill:#349184;}
.class_vip8
{
    fill: #318F82;
    stroke: #1D1E1C;
    stroke-width: 0.5416149;
    stroke-miterlimit: 10;
}

</style>
