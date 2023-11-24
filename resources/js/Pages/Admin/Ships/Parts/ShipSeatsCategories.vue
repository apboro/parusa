<template>
    <div>
        <GuiContainer v-for="seat_category in seat_categories" w-30 mt-30>

            <GuiValue :title="'Категория'" v-if="!editing">{{ seat_category.category_name }}</GuiValue>
            <DictionaryDropDown v-else
                                :dictionary="'seat_categories'"
                                v-model="seat_category.category"
            />

            <GuiValue :title="'Начальное место'" v-if="!editing">{{seat_category.start_seat_number}}</GuiValue>
            <InputNumber w-20 v-else v-model="seat_category.start_seat_number"/>

            <GuiValue :title="'Конечное место'" v-if="!editing"> {{seat_category.end_seat_number}} </GuiValue>
            <InputNumber v-else v-model="seat_category.end_seat_number"/>
            <GuiButton style="margin-top: 10px" v-if="editing" @clicked="removeSeatCategory(seat_category)"
                       :color="'red'">-</GuiButton>
        </GuiContainer>

        <GuiButton style="margin-top: 30px" v-if="editing" @clicked="addSeatCategory">Добавить категорию</GuiButton>
        <GuiContainer mt-20 t-15 v-if="editable">
            <GuiButton v-if="!editing" @click="edit">Редактировать</GuiButton>
            <div v-else>
                <GuiButton :color="'green'" @click="save">Сохранить</GuiButton>
                <GuiButton :color="'red'" @click="cancel">Отмена</GuiButton>
            </div>
        </GuiContainer>
    </div>
</template>

<script>
import GuiContainer from "@/Components/GUI/GuiContainer";
import GuiValue from "@/Components/GUI/GuiValue";
import GuiValueArea from "@/Components/GUI/GuiValueArea";
import GuiActivityIndicator from "@/Components/GUI/GuiActivityIndicator";
import GuiButton from "@/Components/GUI/GuiButton";
import FormPopUp from "@/Components/FormPopUp";
import FormDictionary from "@/Components/Form/FormDictionary";
import DictionaryDropDown from "@/Components/Inputs/DictionaryDropDown.vue";
import InputNumber from "@/Components/Inputs/InputNumber.vue";

export default {
    props: {
        shipId: {type: Number, required: true},
        data: {type: Object, required: true},
        editable: {type: Boolean, default: false},
    },

    emits: ['update'],

    components: {
        InputNumber,
        DictionaryDropDown,
        FormDictionary,
        FormPopUp,
        GuiButton,
        GuiActivityIndicator,
        GuiValueArea,
        GuiValue,
        GuiContainer
    },

    data: () => ({
        editing: false,
        seat_categories: [
            {
                category: null,
                start_seat_number: null,
                end_seat_number: null,
            }
        ]
    }),

    created() {
        axios.post('/api/ship/seat_categories/get',{id: this.shipId}).then(response => this.seat_categories = response.data.data);
    },

    methods: {
        addSeatCategory() {
            this.seat_categories.push({
                category: null,
                start_seat_number: null,
                end_seat_number: null,
            });
        },
        removeSeatCategory(seatCategoryToRemove) {
            const index = this.seat_categories.indexOf(seatCategoryToRemove);
            if (index !== -1) {
                this.seat_categories.splice(index, 1);
            }
        },
        edit() {
            this.editing = !this.editing;
        },
        cancel() {
            this.editing = false;
        },
        save() {
            axios.post('/api/ship/seat_categories/update', {
                shipId: this.shipId,
                seatCategories: this.seat_categories,
            })
                .then((response) => {
                    this.$toast.success(response.data.message, 5000);
                })
                .catch(error => {
                    this.$toast.error(error.response.data.message, 5000);
                })
                .finally(() => {
                });
        }
    }
}
</script>
