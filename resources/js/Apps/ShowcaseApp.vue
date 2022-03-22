<template>
    <LoadingProgress :loading="is_initializing || is_queued || is_loading" :opacity="100">
        <GuiMessage v-if="has_error">Ошибка: {{ error_message }}</GuiMessage>
        <MainPage v-else
                  :partner="partner"
                  :base_url="base_url"
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

export default {
    name: "ShowcaseApp",

    components: {GuiMessage, LoadingProgress, MainPage},

    data: () => ({
        partner: null,
        base_url: 'https://parusa.opxcms.com', // without trailing slash

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
        const config = JSON.parse(configElement.innerHTML);
        this.partner = Number(config['partner']);
        this.init();
    },

    methods: {
        init() {
            const urlParams = new URLSearchParams(window.location.search);
            const partner = urlParams.get('partner');
            const media = urlParams.get('media');
            axios.post(this.base_url + '/showcase/init', {partner: partner ? partner : this.partner, media: media})
                .then(response => {
                    this.today = response.data['today'];
                    this.date_from = response.data['date_from'];
                    this.date_to = response.data['date_to'];
                    this.programs = response.data['programs'];
                })
                .catch(error => {
                    this.error_message = error.response.data['message'];
                    this.has_error = true;
                })
                .finally(() => {
                    this.is_initializing = false;
                });
        },

        getList(search) {
            if (this.is_initializing) {
                setTimeout(() => this.getList(search), 300);
                this.is_queued = true;
                return;
            }
            this.is_loading = true;
            this.is_trips_loaded = false;
            axios.post(this.base_url + '/showcase/trips', {search})
                .then(response => {
                    this.trips = response.data['trips'];
                    this.date = response.data['date'];
                    this.is_trips_loaded = true;
                })
                .catch(error => {
                    this.error_message = error.response.data['message'];
                    this.has_error = true;
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
            axios.post(this.base_url + '/showcase/trip', {id: id})
                .then(response => {
                    this.trip = response.data['trip'];
                })
                .catch(error => {
                    this.error_message = error.response.data['message'];
                    this.has_error = true;
                })
                .finally(() => {
                    this.is_loading = false;
                    this.is_queued = false;
                });
        },
    }
}
</script>
