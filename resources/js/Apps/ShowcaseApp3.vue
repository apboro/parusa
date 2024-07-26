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
            <TripsV3List v-if="!order_secret && state.is_initializing === false"
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
                         :crm_url="crmUrl"
                         :debug="debug"
                         :session="session"
                         @search="loadList"
                         @select_trip="selectTrip"
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
import TripsV3List from "@/Pages/ShowcaseV3/TripsV3List.vue";
import {useShowcase3Store} from "@/Stores/showcase3-store";
import {mapStores} from "pinia";

export default {

    props: {
        crm_url: {type: String, default: 'https://lk.excurr.ru'},
        debug: {type: Boolean, default: false},
        excursion: {
            type: Object,
            default: null
        }
    },

    components: {
        TripsV3List,
        TicketsSelectV2,
        OrderInfo,
        ShowcaseLoadingProgress,
        ShowcaseMessage,
    },

    computed: {
        crmUrl() {
            return this.crm_url_override ? this.crm_url_override : this.crm_url;
        },
        ...mapStores(useShowcase3Store),
    },

    data: () => ({
        crm_url_override: null,
        session: null,
        options: {
            excursions: [],
            partner: null,
            media: null,
            is_partner_page: true,
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
            if (typeof config['excursions'] !== "undefined" && typeof config['excursions'] === 'object') {
                this.options.excursions = config['excursions'];
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

        if (urlParams.has('excursions[]')) {
            this.options.excursions = urlParams.getAll('excursions[]');
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

    watch: {
        excursion: {
            handler(newVal) {
                if (newVal !== null) {
                    this.options.excursions.push(newVal.id);
                }
            },
            deep: true,
            immediate: true
        }
    },
    mounted() {
        const el = document.querySelector('#ap-showcase3');
        if (el) {
            el.style.width = '100%';
        }
    },

    methods: {
        handleSelectExcursion(selected_excursion_id) {
            this.showcase3Store.excursion = selected_excursion_id;
            this.selected_excursion_id = selected_excursion_id;
            this.loadList()
        },
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
                axios.post(this.url('/showcase_v3/init'), {
                    is_partner: this.options.is_partner_page,
                    partner_id: this.options.partner,
                    excursions: this.options.excursions,
                    media: this.options.media
                })
                    .then(response => {
                        this.today = response.data['today']; // Current date
                        this.search_options.date_from = response.data['date_from']; //  Date range start
                        this.search_options.date_to = response.data['date_to']; // Date range end for future use
                        this.search_options.programs = response.data['programs'];
                        this.search_options.dates = response.data['dates'];
                        this.search_options.items = response.data['items'];
                        this.search_options.checked = response.data['checked'];
                        this.excursions = response.data['excursions'];
                        this.showcase3Store.excursion = this.excursions[0].id;
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
                this.last_search = (this.last_search === null) ? {date: this.search_options.checked ?? this.today} : this.last_search;
            }
            this.trips.is_loading = true;
            axios.post(this.url('/showcase_v3/trips'), {
                search: this.last_search,
                excursion_id: this.showcase3Store.excursion,
            }, {headers: {'X-Ap-External-Session': this.session}})
                .then(response => {
                    this.trips.list = response.data['trips'];
                    this.trips.date = response.data['date'];
                    this.trips.next_date = response.data['next_date'];
                    this.trips.next_date_caption = response.data['next_date_caption'];
                    this.search_options.checked = this.last_search.date;
                    this.showcase3Store.trip = response.data['trips'][0]
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
            let url = new URL(window.location.href);
            if (trip_id !== null) {
                // url = url + '&ap-tid=' + trip_id;
                url.searchParams.set('ap-tid', trip_id);
            } else {
                // url = this.removeParam("ap-tid", url);
                url.searchParams.delete('ap-tid');
            }

            if (trip_id !== this.trip_id) {
                window.history.pushState({}, '', url.toString());
            }
            this.trip_id = trip_id;
            this.updateState();
        },

        removeParam(key, sourceURL) {
            let splitUrl = sourceURL.search.split('?'),
                rtn = splitUrl[0],
                param,
                params_arr = [],
                queryString = (sourceURL.search.indexOf("?") !== -1) ? splitUrl[1] : '';
            if (queryString !== '') {
                params_arr = queryString.split('&');
                for (var i = params_arr.length - 1; i >= 0; i -= 1) {
                    param = params_arr[i].split('=')[0];
                    if (param === key) {
                        params_arr.splice(i, 1);
                    }
                }
                rtn = rtn + '?' + params_arr.join('&');
            }
            return rtn;
        },

        /**
         * Load trip info and rates.
         *
         * @param trip_id
         */
        getTrip(trip_id) {
            this.trip.is_loading = true;
            axios.post(this.url('/showcase_v2/trip'), {id: trip_id}, {headers: {'X-Ap-External-Session': this.session}})
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
            axios.post(this.url('/showcase_v2/order/info'), {secret: order_secret}, {headers: {'X-Ap-External-Session': this.session}})
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
