<template>
    <div class="ap-showcase__tickets">
        <table v-if="trip.rates" class="ap-showcase__tickets-table">
            <thead>
            <tr>
                <th class="ap-showcase__tickets-table-col-1">Тип билета</th>
                <th class="ap-showcase__tickets-table-col-2">Стоимость</th>
                <th class="ap-showcase__tickets-table-col-3">Количество</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="rate in trip.rates">
                <td data-label="Тип билета:" class="ap-showcase__tickets-table-col-1">
                    {{ rate['name'] }}
                </td>
                <td data-label="Стоимость:" class="ap-showcase__tickets-table-col-2">
                    {{ rate['base_price'] }} руб.
                </td>
                <td class="ap-showcase__tickets-table-col-3">
                    <ShowcaseFormNumber class="ap-showcase__tickets-quantity" :form="form"
                                        :name="'rate.' + rate['grade_id'] + '.quantity'" :hide-title="true"
                                        :quantity="true" :min="0" :border="false" @change="handleChange"/>
                </td>
            </tr>
            <tr v-if="trip.rates">
                <td colspan="3" class="ap-showcase__tickets-table-col-total-title">Итого:</td>
                <td class="ap-showcase__tickets-table-col-total"
                    :class="{'ap-showcase__tickets-filled': count > 0}">{{ this.showcase3Store.getPrice ?? 0 }} руб.
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
import ShowcaseFormNumber from "@/Pages/Showcase/Components/ShowcaseFormNumber.vue";
import ShowcaseIconSign from "@/Pages/Showcase/Icons/ShowcaseIconSign.vue";
import DynamicSchemeContainer from "@/Pages/Admin/Ships/SeatsSchemes/DynamicSchemeContainer.vue";
import SelectedTickets from "@/Pages/Parts/Seats/SelectedTickets.vue";
import ShowcaseFieldNumber from "@/Pages/Showcase/Components/ShowcaseFieldNumber.vue";
import form from "@/Core/Form";
import {useShowcase3Store} from "@/Stores/showcase3-store";
import {mapStores} from "pinia";

export default {
    name: "TicketsSelectV3",
    components: {ShowcaseFieldNumber, SelectedTickets, DynamicSchemeContainer, ShowcaseIconSign, ShowcaseFormNumber},
    emits: ['changeTickets'],
    props: {
        crm_url: String,
        session: String,
    },
    data: () => ({
        form: null,
        message: null,
        activeBackward: false,
        checkedBackward: true,
    }),
    watch: {
        checkedBackward(value) {
            if (value === false) {
                this.activeBackward = false;
                this.backwardTripId = null;
            }
        },

    },
    computed: {
        ...mapStores(useShowcase3Store),

        trip() {
            if (this.showcase3Store.trip.excursion_id !== this.showcase3Store.excursion) {
                this.initForm();
            }

            return this.showcase3Store.trip;
        },
        count() {
            if (this.trip === null) {
                return 0;
            }
            let count = 0;
            this.trip.rates.map(rate => {
                count += this.form.values['rate.' + rate['grade_id'] + '.quantity'];
            });
            return count;
        },
    },
    created() {
        this.form = form(null, null);
        if (this.trip) {
            this.initForm();
        }
    },
    methods: {
        handleChange() {
            this.showcase3Store.ticketsData =this.form.values;
            this.$emit('changeTickets', this.form.values);
        },
        initForm() {
            this.form.reset();
            this.trip.rates.map(rate => {
                this.form.set('rate.' + rate['grade_id'] + '.quantity', 0, 'integer|min:0', 'Количество', true);
            });
            this.form.load();
        },
        multiply(a, b) {
            return Math.ceil(a * b * 100) / 100;
        },
    }
}
</script>


<style scoped lang="scss">

</style>
