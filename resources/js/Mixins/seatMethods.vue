<script>

export default {
    name: "seatMethods",
    data: () => ({
        selectedSeats: [],
        seatCategory: null,
        categoryColors: {'Standard': '#ACD08F', 'VIP-8': '#318F82', 'VIP-5': '#318F82'},
    }),

    methods: {
        schemeTotal() {
            return this.tickets.reduce((total, ticket) => total + ticket.price, 0);
        },
        getGrades(categoryId) {
            let sort = this.grades.filter(el => el.seat_category_id === categoryId)
            return sort.map(el => el.grade.name);
        },
        getCategoryName(categoryId) {
            return this.categories.find(el => el.id === categoryId).name;
        },

        handleClick(seatNumber) {
            console.log(seatNumber)
            let seat = this.seats.find(el => el.seat_number == seatNumber);
            if (seat.status && [5, 10].includes(seat.status.id)) {
                return;
            }

            if (this.editing === false && this.selecting === false) {
                return;
            }
            if (this.selectedSeats.includes(seat.seat_id)) {
                this.selectedSeats = this.selectedSeats.filter(s => s !== seat.seat_id);
                this.$emit('selectSeat', {
                    seatId: seat.seat_id,
                    seatNumber: seatNumber,
                    selectedSeats: this.selectedSeats,
                    deselect: true
                })
                return;
            }

            this.selectedSeats.push(seat.seat_id)
            this.$emit('selectSeat', {seatNumber: seatNumber, seatId: seat.seat_id, selectedSeats: this.selectedSeats})
        },

        selected(seatNumber) {
            let seat = this.seats.find(el => el.seat_number == seatNumber);

            if (seat.status && [5, 10].includes(seat.status.id)) {
                return 'ap-occupied';
            }
            if (this.selectedSeats.includes(seat.seat_id)) {
                return 'ap-selected';
            }
            if (seat.category.name === 'Standard')
                return 'st22';

            if (seat.category.name === 'VIP-8')
                return 'st30';
        },
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
    }
}


</script>

<style>
.ap-tickets-box {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-top: 10px;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 16px;
    margin-bottom: 16px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    background-color: #fff;
}
.ap-tickets-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-top: 10px;
    padding: 16px;
    margin-bottom: 16px;
}
</style>


