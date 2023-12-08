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
            :selecting="true"
            @selectSeat="handleSelectSeat"/>

        <div v-if="tickets.length > 0" class="tickets-box">
            <span>Выбранные билеты:</span>
            <div v-for="ticket in tickets" class="tickets-box">
                <span><b>Место:</b> {{ ticket.seatNumber }}</span>
                <span><b>Билет:</b> {{ ticket.grade.name }}</span>
                <span v-if="ticket.menu !== null"><b>Меню:</b> {{ ticket.menu.name }}</span>
                <span><b>Цена:</b> {{ ticket.price }} руб.</span>
            </div>
            <span style="margin-top: 15px;"><b>Итого:</b> {{ total }} руб.</span>
        </div>

        <PopUp ref="category"
               :buttons="[
               {result: 'ok', caption: 'OK', color: 'green', disabled: !selectedGrade || (selectedGrade.menus.length > 0 ? !selectedMenu : false)},
               {result: 'cancel', caption: 'Отмена'},
           ]"
        >
            <label v-for="(grade, index) in seatGrades" :key="index">
                <input @click="selectedMenu = null" style="margin-top: 10px" type="radio" v-model="selectedGrade" :value="grade.grade"
                       :name="'grade-select'"> {{ grade.grade.name }} - {{ getGradePrice(grade.grade.id) }} руб.
            </label>
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
import FormNumber from "@/Components/Form/FormNumber";
import PopUp from "@/Components/PopUp";
import InputCheckbox from "@/Components/Inputs/InputCheckbox.vue";
import DynamicSchemeContainer from "@/Pages/Admin/Ships/SeatsSchemes/DynamicSchemeContainer.vue";


export default {
    components: {
        DynamicSchemeContainer,
        InputCheckbox,
        PopUp,
        FormNumber,
    },

    props: {},

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
        handleSelectSeat(data) {
            if (!data.deselect) {
                let categoryId = this.trip['seats'].find(el => el.seat_id === data.seatId).category.id;
                this.seatGrades = this.getFilteredGrades(categoryId);
                this.$refs.category.show().then(() => {
                    this.tickets.push({
                        seatId: data.seatId,
                        seatNumber: data.seatNumber,
                        menu: this.selectedMenu,
                        grade: this.selectedGrade,
                        price: this.getGradePrice(this.selectedGrade.id)
                    })
                })
            } else {
                this.tickets = this.tickets.filter(ticket => ticket.seatId !== data.seatId);
            }
            this.selectedSeats = data.selectedSeats;
        },

        getGradePrice(gradeId) {
            return this.trip['rates'].find(e => e.id === gradeId).value;
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
                    this.$store.dispatch('partner/refresh');
                    this.$refs.popup.hide();
                    if (result === 'add_and_redirect') {
                        this.$router.push({name: 'order'});
                    }
                    return true;
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

.tickets-box {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-top: 10px;
    border: 1px solid #ddd; /* Add a border */
    border-radius: 8px; /* Add rounded corners */
    padding: 16px; /* Add padding */
    margin-bottom: 16px; /* Add margin between cards */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Add a subtle box shadow */
    background-color: #fff; /* Set background color */
}

</style>
