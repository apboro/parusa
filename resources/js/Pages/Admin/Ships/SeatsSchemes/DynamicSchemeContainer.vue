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
import scheme139 from "@/Pages/Admin/Ships/SeatsSchemes/Scheme139.vue";
import LoadingProgress from "@/Components/LoadingProgress.vue";
import scheme140 from "@/Pages/Admin/Ships/SeatsSchemes/Scheme140.vue";
import schemeAstra from "@/Pages/Admin/Ships/SeatsSchemes/SchemeAstra.vue";
import schemeAstraMeteor from "@/Pages/Admin/Ships/SeatsSchemes/SchemeAstraMeteor.vue";

export default {
    components: {
        LoadingProgress,
        scheme12: scheme12,
        scheme3: scheme3,
        scheme58: scheme58,
        scheme132: scheme132,
        schemeAstra: schemeAstra,
        scheme139: scheme139,
        scheme140: scheme140,
        schemeAstraMeteor: schemeAstraMeteor
    },
    emits: ['selectSeat'],
    props: {
        data: Object,
        shipId: Number,
        scheme_name: String,
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
            switch (this.scheme_name) {
                case 'astra':
                    return schemeAstra
                case 'astra_meteor':
                    return schemeAstraMeteor
                case 3:
                    return scheme3
                case 58:
                    return scheme58
                case 132:
                    return scheme132
                case 139:
                    return scheme139
                case 140:
                    return scheme140
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
.not-set {
    fill: cyan;
}
.seat_number{pointer-events: none}
.class_standard{fill: #ACD08F;}
.class_vip5{fill:#349184;}
.class_business{fill: #f6a68c;}
.class_comfort{fill: #759fd6;}
.class_vip8
{
    fill: #318F82;
    stroke: #1D1E1C;
    stroke-width: 0.5416149;
    stroke-miterlimit: 10;
}

</style>
