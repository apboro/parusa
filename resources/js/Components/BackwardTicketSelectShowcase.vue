<script>
import GuiMessage from "@/Components/GUI/GuiMessage.vue";
import GuiContainer from "@/Components/GUI/GuiContainer.vue";
import GuiHeading from "@/Components/GUI/GuiHeading.vue";
import InputDate from "@/Components/Inputs/InputDate.vue";
import GuiButton from "@/Components/GUI/GuiButton.vue";
import ListTableRow from "@/Components/ListTable/ListTableRow.vue";
import ListTable from "@/Components/ListTable/ListTable.vue";
import ListTableCell from "@/Components/ListTable/ListTableCell.vue";
import TripsList from "../Pages/Admin/Trips/Parts/TripsList.vue";
import ListTableResponsiveCell from "./ListTable/ListTableResponsiveCell.vue";
import ListTableResponsiveRow from "./ListTable/ListTableResponsiveRow.vue";


export default {
    components: {
        ListTableResponsiveRow,
        ListTableResponsiveCell,
        TripsList,
        ListTableCell, ListTable, ListTableRow, GuiButton,
        InputDate, GuiHeading, GuiContainer, GuiMessage,
    },

    props: {
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
    }),


    mounted() {
        this.getBackwardTrips(this.trip.id)
    },

    methods: {
        getBackwardTrips($tripId) {
            if ($tripId === 0) {
                this.backward_trips = [];
                return;
            }
            this.returning_progress = true;
            axios.post('/showcase/get_backward_trips', {
                tripId: $tripId
            },{headers: {'X-Ap-External-Session': this.session}})
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
    <GuiContainer v-if="backward_trips && backward_trips.length" mb-50>

        <ListTableResponsive
            :titles="['Отправление', '№ Рейса', 'Экскурсия', '']">
            <ListTableResponsiveRow v-for="backward_trip in backward_trips">
                <ListTableResponsiveCell>
                    <div>
                        <b>Время:
                            {{ backward_trip['start_time'] }}
                        </b>
                    </div>
                    <div>Дата: {{ backward_trip['start_date'] }}</div>
                </ListTableResponsiveCell>
                <ListTableResponsiveCell>
                  Рейс:
                    {{ backward_trip['excursion'] }}
                </ListTableResponsiveCell>
                <ListTableResponsiveCell class="va-middle">
                    <GuiButton :color="'red'" @clicked="addBackwardTickets(backward_trip.id)"
                               :class="{ 'chosen-button': tripChosen && backward_trip['id'] === this.tripChosenId}">
                        {{ tripChosen && backward_trip['id'] === this.tripChosenId ? 'Выбрано' : 'Выбрать' }}
                    </GuiButton>
                </ListTableResponsiveCell>
            </ListTableResponsiveRow>
        </ListTableResponsive>
    </GuiContainer>


</template>

<style scoped lang="scss">
.chosen-button {
    background-color: orange;
    color: black;
}
</style>
