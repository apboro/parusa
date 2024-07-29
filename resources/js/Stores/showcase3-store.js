import { defineStore } from "pinia";
import { ref, computed } from "vue";

export const useShowcase3Store = defineStore('showcase3', () => {
    const excursion = ref(null);
    const trip = ref(null);
    const backwardTrip = ref(null);
    const promocode = ref('');
    const ticketsData = ref([]);
    const tickets = ref([]);
    const discount = ref({
        status: false,
        discount_price: null,
        discounted: null,
        full_price: null,
    });
    const contactInfo = ref({
        name: null,
        phone: null,
        email: null
    });


    const getTotal = computed(() => {
        if (trip.value === null) {
            return null;
        }
        if (trip.value.trip_with_seats && tickets.value.length > 0) {
            ticketsData.value = [];
            return tickets.value.reduce((sum, ticket) => sum + ticket.price, 0);
        }
        if (ticketsData.value.length === 0){
            return null;
        }
        let total = 0;
        tickets.value = [];
        trip.value.rates.forEach(rate => {
            total += multiply(rate.base_price, ticketsData.value[`rate.${rate.grade_id}.quantity`]);
            if (backwardTrip.value) {
                total += multiply(rate.backward_price, ticketsData.value[`rate.${rate.grade_id}.quantity`]);
            }
        });
        return multiply(total, 1);
    });

    const getPrice = computed(() => {
        return (discount.value.discount_price === 0 || discount.value.discount_price === null)
            ? getTotal.value
            : (getTotal.value - discount.value.discounted);
    });

    function multiply(a, b) {
        return Math.ceil(a * b * 100) / 100;
    }

    return {
        excursion,
        trip,
        backwardTrip,
        promocode,
        discount,
        contactInfo,
        ticketsData,
        tickets,
        getTotal,
        getPrice,
    };
});
