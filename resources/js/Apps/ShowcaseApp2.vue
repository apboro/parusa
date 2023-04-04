<template>
    <Trips2List
                :date_from="search_options.date_from"
                :date_to="search_options.date_to"
                :programs="search_options.programs"
                :today="today"
                :date="trips.date"
                :dates="search_options.dates"
                :items="search_options.items"
                :checked="search_options.checked"
                :trips="trips.list"
                :next_date="trips.next_date"
                :next_date_caption="trips.next_date_caption"
                :is-loading="trips.is_loading"
                :last-search="last_search"
                :crm_url="crmUrl"
                :debug="debug"
                :session="session"
                @search="loadList"
                @select="selectTrip"
    />
</template>

<script>
import ShowcaseMessage from "@/Pages/Showcase/Components/ShowcaseMessage";
import ShowcaseLoadingProgress from "@/Pages/Showcase/Components/ShowcaseLoadingProgress";
import TicketsSelect from "@/Pages/Showcase/TicketsSelect";
import OrderInfo from "@/Pages/Showcase/OrderInfo";
import Trips2List from "@/Pages/Showcase/Trips2List.vue";

export default {
    props: {
        crm_url: {type: String, default: 'https://cp.parus-a.ru'},
        debug: {type: Boolean, default: false},
    },

    components: {
        Trips2List,
        OrderInfo,
        TicketsSelect,
        ShowcaseLoadingProgress,
        ShowcaseMessage,
    },

    data: () => ({
        crm_url_override: null,
        session: null,
        options: {
            excursions: null,
            partner: null,
            media: null,
            is_partner_page: true,
        },
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
            checked: [],
        },
        last_search: null,

        today: null,

        trips: {
            date: null,
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

        autoload: false,
        excursion_id: null,
    }),

    created() {
        // get config defined outside
        const configElement = document.getElementById('ap-showcase-config');
        const config = configElement !== null ? JSON.parse(configElement.innerHTML) : null;
        if (config !== null) {
            if (typeof config['partner_site'] !== "undefined" && config['partner_site'] === false) {
                this.options.is_partner_page = false;
            }
            if (typeof config['autoload'] !== "undefined" && config['autoload'] === true) {
                this.autoload = true;
            }
            if (typeof config['excursion_id'] !== "undefined" && typeof config['excursion_id'] === 'number') {
                this.excursion_id = config['excursion_id'];
            }
            this.crm_url_override = config['crm_url_override'];
        }

        // get initial parameters
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('partner')) {
            this.options.partner = Number(urlParams.get('partner'));
        } else {
            this.options.partner = config !== null && typeof config['partner'] !== "undefined" && config['partner'] !== null ? Number(config['partner']) : null;
        }
        if (urlParams.has('excursions')) {
            this.options.excursions = urlParams.get('excursions');
        }
        this.options.media = urlParams.get('media');

        if (urlParams.has('ap_showcase_date')) {
            if (this.last_search === null) {
                this.last_search = {};
            }
            this.last_search['date'] = urlParams.get('ap_showcase_date');
        }
        if (urlParams.has('ap_showcase_persons')) {
            if (this.last_search === null) {
                this.last_search = {};
            }
            this.last_search['persons'] = Number(urlParams.get('ap_showcase_persons'));
        }
        if (urlParams.has('ap_showcase_programs')) {
            if (this.last_search === null) {
                this.last_search = {};
            }
            this.last_search['programs'] = Number(urlParams.get('ap_showcase_programs'));
        }

        // get order secret if set
        this.order_secret = localStorage.getItem('ap-showcase-order-secret');

        // get trip id if set
        this.trip_id = this.getTripId();

        // initialize
        this.init();

        // navigation buttons events
        window.addEventListener('popstate', this.handleNavigation);
    },

    computed: {
        crmUrl() {
            return this.crm_url_override ? this.crm_url_override : this.crm_url;
        },
    },

    methods: {
        /**
         * Helper function for url making.
         *
         * @param path
         * @returns {string}
         */
        url(path) {
            return this.crmUrl + path + (this.debug ? '?XDEBUG_SESSION_START=PHPSTORM' : '');
        },

        /**
         * Get externally set internal trip_id from query string.
         */
        getTripId() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('ap-tid')) {
                return Number(urlParams.get('ap-tid'));
            }
            return null;
        },

        /**
         * Initialization request to get initial parameters and create a session.
         *
         * @returns {Promise}
         */
        init() {
            return new Promise((resolve, reject) => {
                axios.post(this.url('/showcase/init2'), {
                    is_partner: this.options.is_partner_page,
                    partner_id: this.options.partner,
                    excursions: this.options.excursions,
                    media: this.options.media
                })
                    .then(response => {
                        this.today = response.data['today']; // Current date
                        this.search_options.date_from = response.data['date_from']; //  Date range start
                        this.search_options.date_to = response.data['date_to']; // Date range end for future use
                        this.search_options.programs = response.data['programs']; // List of available programs
                        this.search_options.dates = response.data['dates']; // List of available programs
                        this.search_options.items = response.data['items']; // List of available programs
                        this.search_options.checked = response.data['checked']; // List of available programs
                        this.session = response.headers['x-ap-external-session'];
                        this.updateState();
                        resolve();
                    })
                    .catch(error => {
                        this.state.error_message = error.response.data['message'];
                        console.log(this.error_message);
                        this.state.has_error = true;
                        reject();
                    })
                    .finally(() => {
                        this.state.is_initializing = false;
                    });
            });
        },

        /**
         * Handle application action on current state.
         */
        updateState() {
            if (this.order_secret !== null) {
                // handle order state loading
                this.getOrderInfo(this.order_secret);

            } else if (this.trip_id !== null) {
                // handle trip info
                this.getTrip(this.trip_id)

            } else if (this.autoload) {
                // handle trips list with autoload
                this.loadList();
            }
        },

        /**
         * Load trips list.
         *
         * @param search
         */
        loadList(search = null) {
            if (search !== null) {
                this.last_search = search;
            } else {
                this.last_search = (this.last_search === null) ? {date: this.today} : this.last_search;
            }
            this.trips.is_loading = true;
            axios.post(this.url('/showcase/trips2'), {
                search: this.last_search,
                excursion_id: this.excursion_id,
                excursions: this.options.excursions,
            }, {headers: {'X-Ap-External-Session': this.session}})
                .then(response => {
                    this.trips.list = response.data['trips'];
                    this.trips.date = response.data['date'];
                    this.trips.next_date = response.data['next_date'];
                    this.trips.next_date_caption = response.data['next_date_caption'];
                })
                .catch(error => {
                    this.state.error_message = error.response.data['message'];
                    this.state.has_error = true;
                })
                .finally(() => {
                    this.trips.is_loading = false;
                });
        },

        /**
         * Handle browser navigation.
         */
        handleNavigation() {
            const trip_id = this.getTripId();
            if (trip_id !== this.trip_id) {
                this.selectTrip(trip_id);
            }
        },

        /**
         * Select trip to choose tickets.
         *
         * @param trip_id
         */
        selectTrip(trip_id) {
            const url = new URL(window.location.href);
            if (trip_id !== null) {
                url.searchParams.set('ap-tid', trip_id);
            } else {
                url.searchParams.delete('ap-tid');
            }

            if (trip_id !== this.trip_id) {
                window.history.pushState({}, '', url.toString());
            }
            this.trip_id = trip_id;
            this.updateState();
        },

        /**
         * Load trip info and rates.
         *
         * @param trip_id
         */
        getTrip(trip_id) {
            this.trip.is_loading = true;
            axios.post(this.url('/showcase/trip'), {id: trip_id}, {headers: {'X-Ap-External-Session': this.session}})
                .then(response => {
                    this.trip.data = response.data['trip'];
                })
                .catch(() => {
                    this.selectTrip(null);
                })
                .finally(() => {
                    this.trip.is_loading = false;
                });
        },

        /**
         * Load order info.
         *
         * @param order_secret
         */
        getOrderInfo(order_secret) {
            this.order.is_loading = true;
            axios.post(this.url('/showcase/order/info'), {secret: order_secret}, {headers: {'X-Ap-External-Session': this.session}})
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
