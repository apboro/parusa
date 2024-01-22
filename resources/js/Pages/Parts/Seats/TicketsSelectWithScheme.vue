<template>
    <PopUp :title="'Добавить в заказ'"
           :manual="true"
           :resolving="resolved"
           :buttons="[
               {result: 'add_and_redirect', caption: 'Добавить и оформить', color: 'green', disabled: tickets.length < 1},
               {result: 'add', caption: 'Добавить', color: 'green', disabled: tickets.length < 1},
               {result: 'cancel', caption: 'Отмена'},
           ]"
           ref="popup"
    >
        <DynamicSchemeContainer
            :data="trip"
            :shipId="trip['shipId']"
            :scheme_name="trip['scheme_name']"
            :selecting="true"
            @selectSeat="handleSelectSeat"/>

        <SelectedTickets v-if="tickets.length > 0" :tickets="tickets"/>

        <PopUp ref="category"
               :buttons="[
               {result: 'ok', caption: 'OK', color: 'green', disabled: !selectedGrade || (selectedGrade.menus.length > 0 ? !selectedMenu : false)},
               {result: 'cancel', caption: 'Отмена'},
           ]"
        >
            <div v-for="(grade, index) in seatGrades" :key="index">
                <label v-if="getGradePrice(grade.grade.id)">
                    <input @click="selectedMenu = null" style="margin-top: 10px" type="radio" v-model="selectedGrade"
                           :value="grade.grade"
                           v-if="getGradePrice(grade.grade.id)"
                           :name="'grade-select'"> {{ grade.grade.name }} - {{ getGradePrice(grade.grade.id) }} руб.
                </label>
            </div>
            <div style="display: flex; flex-direction: column; align-items: center"
                 v-if="selectedGrade && selectedGrade.menus.length > 0">
                <span style="margin-top: 20px;">Выберите меню</span>
                <label v-for="(menu, index) in selectedGrade.menus" :key="index">
                    <input style="margin-top: 10px;" type="radio" v-model="selectedMenu" :value="menu"
                           :name="'menu-select'"> {{ menu.name }}
                </label>
            </div>
        </PopUp>
    </PopUp>
</template>

<script>
import FormNumber from "@/Components/Form/FormNumber.vue";
import PopUp from "@/Components/PopUp.vue";
import InputCheckbox from "@/Components/Inputs/InputCheckbox.vue";
import DynamicSchemeContainer from "@/Pages/Admin/Ships/SeatsSchemes/DynamicSchemeContainer.vue";
import seatMethods from "@/Mixins/seatMethods.vue";
import SelectedTickets from "@/Pages/Parts/Seats/SelectedTickets.vue";


export default {
    components: {
        SelectedTickets,
        DynamicSchemeContainer,
        InputCheckbox,
        PopUp,
        FormNumber,
    },
    mixins: [seatMethods],

    props: {payload: Object},

    data: () => ({
        trip: null,
        selectedSeats: [],
        seatGrades: null,
        selectedGrade: null,
        selectedMenu: null,
        tickets: [],
    }),

    computed: {
        total() {
            return this.tickets.reduce((total, ticket) => total + ticket.price, 0);
        },
    },

    methods: {
        getGradePrice(gradeId) {
            return this.trip['rates'].find(e => e.id === gradeId)?.value;
        },
        getFilteredGrades(categoryId) {
            return this.trip['seat_tickets_grades'].filter(el => el.seat_category_id === categoryId)
        },
        multiply(a, b) {
            return Math.ceil(a * b * 100) / 100;
        },

        handle(trip) {
            this.trip = trip;
            this.$refs.popup.show();
        },

        resolved(result) {
            if (['add', 'add_and_redirect'].indexOf(result) === -1 || this.count === 0) {
                this.selectedGrade = null;
                this.selectedSeats = null;
                this.selectedMenu = null;
                this.trip.loading = true;
                this.tickets = [];
                this.$refs.popup.hide();
                return true;
            }
            this.$refs.popup.process(true);
            axios.post('/api/cart/scheme/add', {
                trip_id: this.trip['id'],
                tickets: this.tickets
            })
                .then(() => {
                    if (this.payload.partner && this.payload.partner.type_id !== 1003) {
                        this.$store.dispatch('partner/refresh');
                    }
                    if (this.payload.partner && this.payload.partner.type_id === 1003) {
                        this.$store.dispatch('promoter/refresh');
                    }
                    this.$refs.popup.hide();
                    if (result === 'add_and_redirect') {
                        this.$router.push({name: 'order'});
                    }
                    return true;
                })
                .catch(error => {
                    this.$toast.error(error.response.data.message, 5000);
                })
                .finally(() => {
                    this.$refs.popup.process(false);
                });
            return false;
        }
    }
}
</script>

<style lang="scss" scoped>
$project_font: -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica Neue, Arial, Noto Sans, sans-serif, Apple Color Emoji, Segoe UI Emoji, Segoe UI Symbol, Noto Color Emoji !default;
$base_black_color: #1e1e1e !default;

.tickets-select-table {
    font-size: 14px;
    font-family: $project_font;
    border-collapse: collapse;
    margin: 10px 0 0;

    & thead {
        color: #424242;

        & td {
            vertical-align: middle;
        }
    }

    & th {
        text-align: left;
        padding: 10px;
        cursor: default;
    }

    & td {
        vertical-align: middle;
        color: $base_black_color;
        padding: 0 10px;
        cursor: default;
        white-space: nowrap;
    }

    &__total {
        font-size: 16px;
        font-weight: bold;
        padding-top: 15px !important;
    }

    &__notes {
        white-space: normal !important;
        max-width: 300px;
    }

    & .input-field {
        width: 140px;
    }

    &:deep(.input-number__input) {
        text-align: center;
    }

    & .input-field__errors-error {
        font-size: 12px;
    }
}

</style>
