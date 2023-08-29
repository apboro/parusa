<script>
import GuiMessage from "@/Components/GUI/GuiMessage.vue";
import GuiContainer from "@/Components/GUI/GuiContainer.vue";
import GuiHeading from "@/Components/GUI/GuiHeading.vue";
import InputDate from "@/Components/Inputs/InputDate.vue";
import GuiButton from "@/Components/GUI/GuiButton.vue";
import ListTableRow from "@/Components/ListTable/ListTableRow.vue";
import ListTable from "@/Components/ListTable/ListTable.vue";
import ListTableCell from "@/Components/ListTable/ListTableCell.vue";


export default {
    components: {
        ListTableCell, ListTable, ListTableRow, GuiButton,
        InputDate, GuiHeading, GuiContainer, GuiMessage,
    },

    data: () => ({
        ticketId: null,
        backward_trips: null,
        tickets: null,
    }),

    methods: {
        getBackwardTrips($tripId, $ticketId, tickets) {
            if ($tripId === 0) {
                this.backward_trips = [];
                return;
            }
            this.tickets = tickets;
            this.ticketId = $ticketId;
            this.returning_progress = true;
            axios.post('/api/order/backward/get_backward_trips', {
                tripId: $tripId,
                ticketId: $ticketId,
            })
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

        addBackwardTicket($backTripId){
            this.returning_progress = true;
            axios.post('/api/order/backward/add_backward_tickets', {
                ticketId: this.ticketId,
                tickets: this.tickets,
                back_trip_id: $backTripId
            }).then(() => {
                this.$emit('update')
            })
            this.returning_progress = false;
        }
    }
}

</script>

<template>
    <GuiContainer v-if="backward_trips && backward_trips.length" mb-50>
        <ListTable :titles="['Отправление', '№ Рейса', 'Экскурсия', 'Осталось билетов', 'Статусы движение / продажа', '']">
            <ListTableRow v-for="trip in backward_trips">
                <ListTableCell>
                    <div>
                        <b>
                            <router-link :class="'link'" :to="{name: 'trip-view', params: {id: trip['id']}}">{{ trip['start_time'] }}</router-link>
                        </b>
                    </div>
                    <div>{{ trip['start_date'] }}</div>
                </ListTableCell>
                <ListTableCell>
                    <router-link :class="'link'" :to="{name: 'trip-view', params: {id: trip['id']}}">{{ trip['id'] }}</router-link>
                </ListTableCell>
                <ListTableCell>
                    {{ trip['excursion'] }}
                </ListTableCell>
                <ListTableCell>
                    {{ trip['tickets_total'] - trip['tickets_count'] }}
                </ListTableCell>
                <ListTableCell>
                    <div>{{ trip['status'] }}</div>
                    <div>{{ trip['sale_status'] }}</div>
                </ListTableCell>
                <ListTableCell class="va-middle">
                    <GuiButton @clicked="addBackwardTicket(trip.id)">Выбрать</GuiButton>
                </ListTableCell>
            </ListTableRow>
        </ListTable>
    </GuiContainer>

</template>

<style scoped lang="scss">

</style>
