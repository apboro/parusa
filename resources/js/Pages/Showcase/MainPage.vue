<template>
    <div class="ap-showcase">
        <ShowcaseMessage v-if="recently_created">Перенапрваление на оплату</ShowcaseMessage>
        <Final v-else-if="order !== null"
               @close="select"
        />
        <Cart v-else-if="trip_id !== null"
              :crm_url="crm_url"
              :debug="debug"
              :trip-id="trip_id"
              :trip="trip"
              @select="select"
              @order="storeSecret"
        />
        <TripSelect v-else
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
    </div>
</template>

<script>
import TripSelect from "@/Pages/Showcase/Parts/TripSelect";
import Cart from "@/Pages/Showcase/Parts/Cart";
import {mapGetters} from "vuex";
import Final from "@/Pages/Showcase/Parts/Final";
import ShowcaseMessage from "@/Pages/Showcase/Components/ShowcaseMessage";

export default {
    components: {
        ShowcaseMessage,
        Final,
        Cart,
        TripSelect,
    },

    props: {
        crm_url: {type: String, required: true},
        debug: {type: Boolean, default: false},
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
        recently_created: false,
    }),

    watch: {
        today(value) {
            this.search.date = value;
        }
    },

    computed: {
        ...mapGetters('showcase', ['trip_id', 'order', 'order_id'])
    },

    methods: {
        find() {
            this.$emit('find', this.search);
        },
        updateSearch(key, value) {
            this.search[key] = value;
        },
        select(trip_id = null) {
            this.$store.commit('showcase/trip_id', trip_id);
            if (trip_id !== null) {
                this.$emit('select', trip_id);
            } else {
                this.$emit('find', this.search);
            }
        },
        storeSecret(secret, order_id) {
            this.$store.commit('showcase/order', secret);
            this.$store.commit('showcase/order_id', order_id);
            this.recently_created = true;
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
