<script>
export default {
    name: "seatMethods",
    data: () => ({
        selectedSeats: [],
        seatCategory: null,
    }),

    methods: {
        getGrades(categoryId) {
            let sort = this.grades.filter(el => el.seat_category_id === categoryId)
            return sort.map(el => el.grade.name);
        },
        getCategoryName(categoryId) {
            return this.categories.find(el => el.id === categoryId).name;
        },
        getColor(seat) {
            let color = 'gray';
            if (!seat.category)
                return color;
            if (seat.status && [5, 10].includes(seat.status.id))
                return color;
            return this.colors(seat.category.id)
        },
        colors(id) {
            const colorMap = {
                1: "pink",
                2: "#8FCBF1FF",
                3: "#94F18FFF",
                4: "#8f80ed",
                5: "#b58850",
                6: "#b39583",
                7: "#adc162",
                8: "#ad77d5",
                9: "#e470ba",
                10: "#9d9978",
            };

            return colorMap[id] || 'gray';
        },
        handleClick(seat) {
            if (this.seats.length > 0) {
                if (seat.status && [5, 10].includes(seat.status.id)) {
                    return;
                }
            }
            if (this.editing === false && this.selecting === false) {
                return;
            }
            if (this.selectedSeats.includes(seat.seat_number)) {
                this.selectedSeats = this.selectedSeats.filter(s => s !== seat.seat_number);
                this.$emit('selectSeat', {seatNumber: seat.seat_number, selectedSeats: this.selectedSeats})
                return;
            }
            this.selectedSeats.push(seat.seat_number)
            this.$emit('selectSeat', {seatNumber: seat.seat_number, selectedSeats: this.selectedSeats})
        },

        selected(seat_number) {
            return this.selectedSeats.includes(seat_number)
        },

    }


}


</script>


