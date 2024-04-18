<script>
import ShowcaseFormNumber from "@/Pages/Showcase/Components/ShowcaseFormNumber.vue";
import ShowcaseIconSign from "@/Pages/Showcase/Icons/ShowcaseIconSign.vue";
import DynamicSchemeContainer from "@/Pages/Admin/Ships/SeatsSchemes/DynamicSchemeContainer.vue";
import SelectedTickets from "@/Pages/Parts/Seats/SelectedTickets.vue";

export default {
    name: "TicketsSelectComposeV2",
    components: {SelectedTickets, DynamicSchemeContainer, ShowcaseIconSign, ShowcaseFormNumber},
    props: {
        rates: Object,
    },
    data: () => ({
    }),
    computed: {
    },
    methods: {}
}
</script>

<template>

    <div class="ap-showcase__tickets">
        <table v-if="rates" class="ap-showcase__tickets-table">
            <thead>
            <tr>
                <th class="ap-showcase__tickets-table-col-1">Тип билета</th>
                <th class="ap-showcase__tickets-table-col-2">Стоимость</th>
                <th class="ap-showcase__tickets-table-col-3">Количество</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="rate in rates">
                <td data-label="Тип билета:" class="ap-showcase__tickets-table-col-1">{{ rate['name'] }}
                    <span class="ap-showcase__sup-sign" v-if="rate['preferential']"><ShowcaseIconSign/></span>
                </td>
                <td data-label="Стоимость:" class="ap-showcase__tickets-table-col-2">{{ rate['base_price'] }}
                    руб.
                </td>
                <td class="ap-showcase__tickets-table-col-3">
                    <input type="number"/>
                </td>
            </tr>
            <tr>
                <td colspan="3" class="ap-showcase__tickets-table-col-total-title">Итого:</td>
            </tr>
            </tbody>
        </table>
    </div>
</template>

<style scoped lang="scss">

</style>
