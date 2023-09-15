<script>
import GuiContainer from "@/Components/GUI/GuiContainer.vue";
import GuiText from "@/Components/GUI/GuiText.vue";
import GuiValue from "@/Components/GUI/GuiValue.vue";
import GuiHeading from "@/Components/GUI/GuiHeading.vue";
import GuiButton from "@/Components/GUI/GuiButton.vue";
import InputCheckbox from "@/Components/Inputs/InputCheckbox.vue";
import ListTable from "@/Components/ListTable/ListTable.vue";
import ListTableResponsiveRow from "@/Components/ListTable/ListTableResponsiveRow.vue";
import ListTableResponsiveCell from "@/Components/ListTable/ListTableResponsiveCell.vue";
import ListTableResponsive from "@/Components/ListTable/ListTableResponsive.vue";
import ListTableRow from "@/Components/ListTable/ListTableRow.vue";
import ListTableCell from "@/Components/ListTable/ListTableCell.vue";

export default {
    name: "CompactTicket",
    components: {
        ListTableCell,
        ListTableRow,
        ListTableResponsive,
        ListTableResponsiveCell,
        ListTableResponsiveRow,
        ListTable, InputCheckbox, GuiButton, GuiHeading, GuiValue, GuiText, GuiContainer},

    data: () => ({
        hidden: true,
        phoneValue: '..показать..',
        selected: []
    }),

    props: {
        data: null,
    },

    emits: ['used', 'close'],

    created() {
      this.selected = this.data.tickets
          .filter(ticket => ticket.ticket_status !== 'Использован')
          .map(ticket => ticket.ticket_id)
    },

    methods: {
        useTicket() {
            axios.post('/api/ticket/qrcode/use', {ticketIds: this.selected})
                .then(response => {
                    this.$toast.success(response.data['message'], 2000);
                })
            this.$emit('used');
        },
        close() {
            this.$emit('close')
        }
    }

}
</script>

<template>
    <GuiText bold v-if="data.notValidTicket">{{ data.notValidTicket }}</GuiText>

    <div v-if="data.tickets.length > 0">
        <GuiHeading bold> ЗАКАЗ </GuiHeading>
        <GuiValue :title="'Номер билета:'">{{ data.tickets[0].order_id }}</GuiValue>
        <GuiValue :title="'Экскурсия:'">{{ data.tickets[0].excursion_name }}</GuiValue>
        <GuiValue :title="'Начало рейса:'">{{ data.tickets[0].trip_start_time }}</GuiValue>
        <GuiValue :title="'Номер рейса:'">{{ data.tickets[0].trip_id }}</GuiValue>
        <GuiValue :title="'Причал:'">{{ data.tickets[0].pier }}</GuiValue>
        <GuiValue v-if="data.tickets[0].customer_fio" :title="'Клиент:'">{{ data.tickets[0].customer_fio }}</GuiValue>
        <GuiValue :title="'Телефон клиента:'"><span @click="phoneValue = data.tickets[0].customer_phone">{{ phoneValue }}</span></GuiValue>
        <GuiValue v-if="data.tickets[0].promocode" :title="'Промокод:'">{{ data.tickets[0].promocode }}</GuiValue>

        <GuiHeading bold> БИЛЕТЫ </GuiHeading>
        <ListTable :titles="['Номер заказа','Статус билета','Тип билета']" class="list-table-check">
            <ListTableRow v-for="ticket in data.tickets">
                <ListTableCell :nowrap="true">
                    <InputCheckbox :label="ticket.ticket_id.toString()" v-model="selected" :value="ticket.ticket_id" small></InputCheckbox>
                </ListTableCell>
                <ListTableCell :nowrap="true">
                    {{ ticket.ticket_status }}
                </ListTableCell>
                <ListTableCell>
                    {{ ticket.type }}
                </ListTableCell>
            </ListTableRow>
        </ListTable>
        <GuiButton v-if="!data.notValidTicket"
                   style="margin-top: 30px"
                   @click="useTicket()"
                   :color="'green'">ОТМЕТИТЬ КАК ИСПОЛЬЗОВАННЫЕ
    </GuiButton>
    <GuiButton style="margin-top: 40px" :color="'red'" @click="close()">ЗАКРЫТЬ</GuiButton>
    </div>
</template>

<style scoped lang="scss">
.list-table-check td {
    padding: 4px;
}
</style>
