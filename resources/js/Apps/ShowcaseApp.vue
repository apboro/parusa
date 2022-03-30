<template>
    <LoadingProgress :loading="is_initializing || is_queued || is_loading" :opacity="100">
        <template v-if="has_error">
            <GuiMessage>Ошибка: {{ error_message }}</GuiMessage>
        </template>
        <MainPage v-else
                  :partner="partner"
                  :crm_url="crm_url"
                  :debug="debug"
                  :today="today"
                  :date="date"
                  :date_from="date_from"
                  :date_to="date_to"
                  :programs="programs"
                  :trips="trips"
                  :trip="trip"
                  :is-trips-loaded="is_trips_loaded"
                  @find="getList"
                  @select="getTrip"
        />
    </LoadingProgress>
</template>

<script>
import MainPage from "@/Pages/Showcase/MainPage";
import LoadingProgress from "@/Components/LoadingProgress";
import GuiMessage from "@/Components/GUI/GuiMessage";
import GuiButton from "@/Components/GUI/GuiButton";

export default {
    name: "ShowcaseApp",

    props: {
        crm_url: {type: String, default: 'https://cp.parus-a.ru'},
        debug: {type: Boolean, default: false},
    },

    components: {GuiButton, GuiMessage, LoadingProgress, MainPage},

    data: () => ({
        partner: null,
        media: null,
        is_partner_page: true,

        is_initializing: true,
        is_queued: false,
        is_loading: false,
        is_trips_loaded: false,

        has_error: false,
        error_message: null,

        today: null,

        date: null,
        date_from: null,
        date_to: null,
        programs: [],

        trips: [],
        trip: null,
    }),

    created() {
        const configElement = document.getElementById('ap-showcase-config');
        const config = configElement !== null ? JSON.parse(configElement.innerHTML) : null;
        const urlParams = new URLSearchParams(window.location.search);

        if (urlParams.has('partner')) {
            this.partner = Number(urlParams.get('partner'));
        } else {
            this.partner = config !== null && typeof config['partner'] !== "undefined" && config['partner'] !== null ? Number(config['partner']) : null;
        }
        this.media = urlParams.get('media');
        if(config !== null && typeof config['partner_site'] !== "undefined" && config['partner_site'] === false) {
            this.is_partner_page = false;
        }

        this.init();
    },

    methods: {
        url(path) {
            return this.crm_url + path + (this.debug ? '?XDEBUG_SESSION_START=PHPSTORM' : '');
        },
        init() {
            return new Promise((resolve, reject) => {
                axios.post(this.url('/showcase/init'), {
                    is_partner: this.is_partner_page,
                    partner_id: this.partner,
                    media: this.media
                })
                    .then(response => {
                        this.today = response.data['today'];
                        this.date_from = response.data['date_from'];
                        this.date_to = response.data['date_to'];
                        this.programs = response.data['programs'];
                        resolve();
                    })
                    .catch(error => {
                        this.error_message = error.response.data['message'];
                        console.log(this.error_message);
                        this.has_error = true;
                        reject();
                    })
                    .finally(() => {
                        this.is_initializing = false;
                    });
            });
        },

        reset() {
            this.$store.commit('showcase/trip_id', null);
            return this.init();
        },

        getList(search) {
            if (this.is_initializing) {
                setTimeout(() => this.getList(search), 300);
                this.is_queued = true;
                return;
            }
            this.is_loading = true;
            this.is_trips_loaded = false;
            axios.post(this.url('/showcase/trips'), {search})
                .then(response => {
                    this.trips = response.data['trips'];
                    this.date = response.data['date'];
                    this.is_trips_loaded = true;
                })
                .catch(error => {
                    this.error_message = error.response.data['message'];
                    console.log(this.error_message);
                    this.has_error = true;
                    this.reset();
                })
                .finally(() => {
                    this.is_loading = false;
                    this.is_queued = false;
                });
        },

        getTrip(id) {
            if (this.is_initializing) {
                setTimeout(() => this.getTrip(id), 300);
                this.is_queued = true;
                return;
            }
            if (id === null) {
                this.trip = null;
                return;
            }
            this.is_loading = true;
            axios.post(this.url('/showcase/trip'), {id: id})
                .then(response => {
                    this.trip = response.data['trip'];
                })
                .catch(() => {
                    // this.error_message = error.response.data['message'];
                    // this.has_error = true;
                    this.reset()
                        .then(() => {
                            this.trip = null;
                        });
                })
                .finally(() => {
                    this.is_loading = false;
                    this.is_queued = false;
                });
        },
    }
}
</script>
