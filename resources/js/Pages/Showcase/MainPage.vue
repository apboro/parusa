<template>
    <div class="ap-showcase">
        <TripSelect v-if="trip_id === null"
                    :partner="partner"
                    :today="today"
                    :date="date"
                    :date_from="date_from"
                    :date_to="date_to"
                    :programs="programs"
                    :trips="trips"
                    :isTripsLoaded="isTripsLoaded"
                    :search-date="search.date"
                    :search-persons="search.persons"
                    :search-programs="search.programs"
                    @updateSearch="updateSearch"
                    @find="find"
                    @select="select"
        />
        <Cart v-if="trip_id !== null"
              :base_url="base_url"
              :trip-id="trip_id"
              :trip="trip"
              @select="select"
              @order="storeSecret"
        />
    </div>
</template>

<script>
import TripSelect from "@/Pages/Showcase/Parts/TripSelect";
import Cart from "@/Pages/Showcase/Parts/Cart";
import {mapGetters} from "vuex";

export default {
    components: {
        Cart,
        TripSelect,
    },

    props: {
        base_url: {type: String, required: true},
        partner: {type: Number, default: null},
        today: {type: String, default: null},
        date: {type: String, default: null},
        date_from: {type: String, default: null},
        date_to: {type: String, default: null},
        programs: {type: Array, default: null},
        trips: {type: Array, default: () => ([])},
        isTripsLoaded: {type: Boolean, default: false},
        trip: {type: Object, default: null},
    },

    emits: ['find', 'select'],

    data: () => ({
        search: {
            date: null,
            persons: null,
            programs: null,
        },
        test: null,
    }),

    watch: {
        today(value) {
            this.search.date = value;
        }
    },

    computed: {
        ...mapGetters('showcase', [
            'trip_id',
        ]),
    },

    methods: {
        find() {
            this.$emit('find', this.search);
        },
        updateSearch(key, value) {
            this.search[key] = value;
        },
        select(trip_id) {
            this.$store.commit('showcase/trip_id', trip_id);
            this.$emit('select', trip_id);
        },
        storeSecret(secret) {

        },
    }
}
</script>

<style lang="scss" scoped>
.ap-showcase {
    display: flex;
    flex-direction: column;
    min-width: 350px;
}
</style>
