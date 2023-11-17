<template>
    <GuiContainer w-50>
        <MarcelScheme :seats="data.seats"
                      :shipId="shipId"
                      :editing="editing"
                      :categories="data.categories"
                      :grades="data.seat_tickets_grades"
                      @update="handleUpdate"/>
        <GuiContainer mt-20 t-15 v-if="editable">
            <GuiButton v-if="!editing" @click="edit">Редактировать</GuiButton>
            <div v-else>
                Категория мест:
                <DictionaryDropDown :dictionary="'seat_categories'"
                                    v-model="seatCategory"
                                    style="margin-bottom: 20px"
                />

                Категории билетов:
                <DictionaryDropDown :dictionary="'ticket_grades'"
                                    v-model="seatGrades"
                                    :multi="true"
                                    :original="seatGrades"
                />
                <GuiButton style="margin-top: 20px" :color="'green'" @click="save">Сохранить</GuiButton>
                <GuiButton :color="'red'" @click="cancel">Отмена</GuiButton>
            </div>
        </GuiContainer>
    </GuiContainer>
</template>

<script>
import GuiContainer from "@/Components/GUI/GuiContainer.vue";
import GuiButton from "@/Components/GUI/GuiButton.vue";
import MarcelScheme from "@/Pages/Admin/Ships/SeatsSchemes/MarcelScheme.vue";
import DictionaryDropDown from "@/Components/Inputs/DictionaryDropDown.vue";

export default {
    components: {DictionaryDropDown, MarcelScheme, GuiButton, GuiContainer},
    props: {
        shipId: {type: Number, required: true},
        data: {type: Object, required: true},
        editable: {type: Boolean, default: false},
    },

    emits: ['update'],

    data: () => ({
        editing: false,
        selectedSeats: [],
        seatCategory: null,
        seatGrades: []
    }),
    methods: {
        handleUpdate(data) {
            this.selectedSeats = data.selectedSeats;
        },
        edit() {
            this.editing = !this.editing;
        },
        cancel() {
            this.editing = false;
            this.selectedSeats = [];
        },
        save() {
            axios.post('/api/ship/seat_categories/update', {
                shipId: this.shipId,
                selectedSeats: this.selectedSeats,
                seatCategory: this.seatCategory,
                seatGrades: this.seatGrades
            })
                .then((response) => {
                    this.$toast.success(response.data.message, 5000);
                })
                .catch(error => {
                    this.$toast.error(error.response.data.message, 5000);
                })
                .finally(() => {
                    location.reload()
                });
        }
    }
}


</script>
