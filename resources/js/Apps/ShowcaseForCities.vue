<template>
    <ShowcaseLoadingProgress :loading="state.is_initializing || state.is_loading" :opacity="100">
        <template v-if="state.has_error">
            <ShowcaseMessage>Ошибка: {{ state.error_message }}</ShowcaseMessage>
        </template>
        <template v-else>
            <OrderInfo v-if="order_secret"
                       :is-loading="order.is_loading"
                       :order-data="order.data"
                       :secret="order_secret"
                       :crm_url="crmUrl"
                       :debug="debug"
                       :session="session"
                       @close="closeOrder"
            />
            <TripsListForCities v-if="!order_secret && state.is_initializing === false"
                         :date_from="search_options.date_from"
                         :date_to="search_options.date_to"
                         :programs="search_options.programs"
                         :today="today"
                         :excursions="excursions"
                         :date="trips.date"
                         :dates="search_options.dates"
                         :items="search_options.items"
                         :checked="search_options.checked"
                         :trips="trips.list"
                         :next_date="trips.next_date"
                         :next_date_caption="trips.next_date_caption"
                         :is-loading="trips.is_loading"
                         :last-search="last_search"
                         :debug="debug"
                         :session="session"
                         @search="loadList"
                         @select_excursion="handleSelectExcursion"
            />

        </template>
    </ShowcaseLoadingProgress>
</template>

<script>
import ShowcaseMessage from "@/Pages/Showcase/Components/ShowcaseMessage";
import ShowcaseLoadingProgress from "@/Pages/Showcase/Components/ShowcaseLoadingProgress";
import OrderInfo from "@/Pages/Showcase/OrderInfo";
import TicketsSelectV2 from "@/Pages/ShowcaseV2/TicketsSelectV2.vue";
import {useShowcase3Store} from "@/Stores/showcase3-store";
import {mapStores} from "pinia";
import TripsListForCities from "@/Pages/ShowcaseForCities/TripsListForCities.vue";

export default {

    props: {
        debug: {type: Boolean, default: false},
        excursion: {
            type: Object,
            default: null
        }
    },

    components: {
        TripsListForCities,
        TicketsSelectV2,
        OrderInfo,
        ShowcaseLoadingProgress,
        ShowcaseMessage,
    },

    computed: {
        ...mapStores(useShowcase3Store),
    },

    data: () => ({
        session: null,
        options: {
            excursions: [],
            partner: null,
            media: null,
            is_partner_page: false,
        },
        excursions: null,
        selected_excursion_id: null,
        state: {
            is_initializing: true,
            is_loading: false,
            has_error: false,
            error_message: null,
        },
        search_options: {
            date_from: null,
            date_to: null,
            programs: [],
            dates: [],
            items: [],
            checked: null,
        },
        last_search: null,

        today: null,

        trips: {
            date: null,
            rates: null,
            list: null,
            next_date: null,
            next_date_caption: null,
            is_loading: false,
        },

        trip_id: null,
        trip: {
            is_loading: false,
            data: null,
        },

        order_secret: null,
        order: {
            is_loading: false,
            data: null,
        },
        autoload: true,
        excursion_id: null,
    }),

    created() {
        // get config defined outside
        const configElement = document.getElementById('ap-showcase-config');
        const config = configElement !== null ? JSON.parse(configElement.innerHTML) : null;
        // get initial parameters
        const urlParams = new URLSearchParams(window.location.search);
        // get order secret if set
        this.order_secret = localStorage.getItem('ap-showcase-order-secret');
    },

    watch: {
        excursion: {
            handler(newVal, oldVal) {
                if (newVal !== null) {
                    this.options.excursions = [];
                    this.options.excursions.push(newVal.id);
                    this.showcase3Store.excursion = newVal.id;
                    this.selected_excursion_id = newVal.id;
                    if (oldVal == null || newVal.id !== oldVal.id) {
                        this.init();
                    }
                }
            },
            immediate: true
        }
    },
    mounted() {
    },

    methods: {
        handleSelectExcursion(selected_excursion_id) {
            this.showcase3Store.excursion = selected_excursion_id;
            this.selected_excursion_id = selected_excursion_id;
            this.init()
        },

        async init() {
            try {
                const response = await axios.post('/cities/init', {
                    is_partner: this.options.is_partner_page,
                    partner_id: this.options.partner,
                    excursions: this.options.excursions,
                    media: this.options.media
                });
                const data = response.data;
                this.today = data['today'];
                this.search_options.date_from = data['date_from'];
                this.search_options.date_to = data['date_to'];
                this.search_options.programs = data['programs'];
                this.search_options.dates = data['dates'];
                this.search_options.items = data['items'];
                this.search_options.checked = data['checked'];
                this.excursions = data['excursions'];
                this.updateState();

            } catch (error) {
                console.log(error)
                this.state.error_message = error.response.data['message'];
                this.state.has_error = true;
            } finally {
                this.state.is_initializing = false;
            }
        },

        /**
         * Handle application action on current state.
         */
        updateState() {
            if (this.order_secret !== null) {
                // handle order state loading
                this.getOrderInfo(this.order_secret);

            } else if (this.autoload) {
                // handle trips list with autoload
                this.loadList();
            }
        },

        async loadList(search = null) {
            if (search !== null) {
                this.last_search = search;
            } else {
                this.last_search = this.last_search === null ? { date: this.search_options.checked ?? this.today } : this.last_search;
            }
            this.trips.is_loading = true;
            try {
                const response = await axios.post('/cities/trips', {
                    search: this.last_search,
                    excursion_id: this.showcase3Store.excursion,
                });
                const data = response.data;
                this.trips.list = data['trips'];
                this.trips.date = data['date'];
                this.trips.next_date = data['next_date'];
                this.trips.next_date_caption = data['next_date_caption'];
                this.search_options.checked = this.last_search.date;
                this.showcase3Store.trip = data['trips'][0];

            } catch (error) {
                console.log(error)
                this.state.error_message = error.response.data['message'];
                this.state.has_error = true;
            } finally {
                this.trips.is_loading = false;
            }
        },

        /**
         * Load order info.
         *
         * @param order_secret
         */
        getOrderInfo(order_secret) {
            this.order.is_loading = true;
            axios.post('/showcase_v2/order/info', {secret: order_secret}, {headers: {'X-Ap-External-Session': this.session}})
                .then(response => {
                    this.order.data = response.data['order'];
                })
                .catch(() => {
                    this.closeOrder();
                })
                .finally(() => {
                    this.order.is_loading = false;
                });
        },

        /**
         * Close order.
         */
        closeOrder() {
            localStorage.clear();
            this.order_secret = null;
            this.selectTrip(null);
        }
    }
}
</script>
