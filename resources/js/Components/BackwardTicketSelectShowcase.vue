<script>

import GuiContainer from "@/Components/GUI/GuiContainer.vue";
import ShowcaseV2Button from "../Pages/ShowcaseV2/Components/ShowcaseV2Button.vue";

export default {
    components: {
        ShowcaseV2Button,
        GuiContainer,
    },

    props: {
        crm_url: {type: String, default: 'https://lk.excurr.ru'},
        session: {type: String, required: true},
        trip: {
            type: Object,
            default: null
        },
    },

    data: () => ({
        backward_trips: null,
        backward_trip_id: null,
        tripChosen: false,
        tripChosenId: null,
        debug: false,
    }),


    mounted() {
        this.getBackwardTrips(this.trip.id)
    },

    methods: {
        url(path) {
            return this.crm_url + path + (this.debug ? '?XDEBUG_SESSION_START=PHPSTORM' : '');
        },

        getBackwardTrips($tripId) {
            if ($tripId === 0) {
                this.backward_trips = [];
                return;
            }
            this.returning_progress = true;
            axios.post(this.url('/showcase/get_backward_trips'), {
                tripId: $tripId
            }, {headers: {'X-Ap-External-Session': this.session}})
                .then((response) => {
                    this.backward_trips = response.data.data['trips'];
                })
                .catch(error => {
                    this.backward_trips = null;
                    this.$toast.error(error.response.data.message, 5000);
                })
                .finally(() => {
                    this.returning_progress = false;
                });
        },

        addBackwardTickets($backward_trip_id) {
            this.tripChosen = this.tripChosenId !== $backward_trip_id ? true : !this.tripChosen;
            this.tripChosenId = $backward_trip_id;
            this.$emit('select-backward-trip', {backward_trip_id: $backward_trip_id, activeBackward: this.tripChosen});
        }
    }
}

</script>

<template>
    <GuiContainer class="ap-button-container">
        <ShowcaseV2Button style="margin: 15px;" v-for="backward_trip in backward_trips"
                          :color="'purple'"
                          @clicked="addBackwardTickets(backward_trip.id)"
                          :class="{ 'ap-chosen-button': tripChosen && backward_trip['id'] === this.tripChosenId}">
            {{ backward_trip['start_time'] }}
        </ShowcaseV2Button>
    </GuiContainer>

</template>

<style scoped lang="scss">
.ap-chosen-button {
    background-color: #E83B4E;
    color: white;
}
.ap-chosen-button:hover {
    background-color: #E83B4E;
    color: white;
}

.ap-button-container {
    display: flex;
    flex-wrap: wrap;
    gap: 2px;
}

</style>
