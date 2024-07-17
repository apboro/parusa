<template>
    <div class="ap-showcase__tickets">
        <p class="ap-showcase__tickets-title">Укажите количество билетов:</p>
        <table v-if="trip.rates" class="ap-showcase__tickets-table">
<!--            <thead>-->
<!--            <tr>-->
<!--                <th class="ap-showcase__tickets-table-col-1">Тип билета</th>-->
<!--                <th class="ap-showcase__tickets-table-col-2">Стоимость</th>-->
<!--                <th class="ap-showcase__tickets-table-col-3">Количество</th>-->
<!--            </tr>-->
<!--            </thead>-->
            <tbody>
                <tr v-for="rate in trip.rates">
                    <td data-label="Тип билета:" class="ap-showcase__tickets-table-col-1">
                        <span class="ap-showcase__tickets-type">{{ rate['name'] }}</span> <br>
                        <span class="ap-showcase__tickets-price" data-label="Стоимость:">{{ rate['base_price'] }} руб.</span>
                    </td>
                    <td class="ap-showcase__tickets-table-col-3">
                        <ShowcaseFormNumber class="ap-showcase__tickets-quantity" :form="form"
                                            :name="'rate.' + rate['grade_id'] + '.quantity'" :hide-title="true"
                                            :quantity="true" :min="0" :border="false" @change="handleChange"/>
                    </td>
                </tr>
                <tr v-if="trip.rates" class="ap-showcase__tickets-table-col-total">
                    <td colspan="3" class="ap-showcase__tickets-table-col-total-title">
                        Итого: <span :class="{'ap-showcase__tickets-filled': count > 0}">{{ this.showcase3Store.getPrice ?? 0 }} ₽</span>
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
    .ap-showcase__tickets {
        width: 282px;
    }

    .ap-showcase__tickets-title {
        width: 100%;
        font-family: Gilroy;
        font-size: 20px;
        font-weight: 700;
        color: #241B5B;
        height: max-content;
    }

    .ap-showcase__tickets-type {
        font-family: Gilroy;
        font-size: 16px;
        font-weight: 500;
        color: #241B5B;
        display: inline-block;
        margin-bottom: 8px;
    }

    .ap-showcase__tickets-price {
        font-family: Gilroy;
        font-size: 14px;
        font-weight: 500;
        color: #0E0E0E;
    }

    .ap-showcase__tickets-table {
        width: 100%;
    }

    .ap-showcase__tickets-table tbody {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .ap-showcase__tickets-table tr {
        display: flex;
        justify-content: space-between;
    }

    .ap-showcase__tickets-table-col-3 {
        width: 120px;
        height: 46px;
        border: 1px solid #DFDFDF;
    }

    .ap-showcase__tickets-table-col-total {
        margin-top: 20px;

        @media screen and (max-width: 769px) {
            margin-top: 0;
        }
    }

    .ap-showcase__tickets-table-col-total-title {
        color: #202124;
        font-family: Segoe UI;
        font-size: 20px;
        font-weight: 400;
    }

    .ap-showcase__tickets-table-col-total-title span {
        color: #E83B4E;
        font-family: Segoe UI;
        font-size: 20px;
        font-weight: 600;
    }
</style>
