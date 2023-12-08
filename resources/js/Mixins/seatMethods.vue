<script>
export default {
    name: "seatMethods",
    data: () => ({
        selectedSeats: [],
        seatCategory: null,
        categoryColors: {'Standard': '#ACD08F', 'VIP-8': '#318F82'},
    }),

    methods: {
        getGrades(categoryId) {
            let sort = this.grades.filter(el => el.seat_category_id === categoryId)
            return sort.map(el => el.grade.name);
        },
        getCategoryName(categoryId) {
            return this.categories.find(el => el.id === categoryId).name;
        },

        handleClick(seatNumber) {
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

    }


}


</script>


