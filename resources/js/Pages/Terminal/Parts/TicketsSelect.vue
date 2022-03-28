<template>
    <PopUp :title="'Добавить в заказ'"
           :manual="true"
           :resolving="resolved"
           :buttons="[
               {result: 'add_and_redirect', caption: 'Добавить и оформить', color: 'green', disabled: count < 1},
               {result: 'add', caption: 'Добавить', color: 'green', disabled: count < 1},
               {result: 'cancel', caption: 'Отмена'},
           ]"
           ref="popup"
    >
        <table class="tickets-select-table">
            <thead>
            <tr>
                <th class="p-10 w-140px">Тип</th>
                <th class="p-10 w-70px">Цена</th>
                <th class="p-10 w-100px">Количество</th>
                <th class="p-10 w-100px">Стоимость</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="key in iterator">
                <td class="pr-10 pt-15">{{ form.values['tickets.' + key + '.grade_name'] }}</td>
                <td class="pr-10 pt-15">{{ form.values['tickets.' + key + '.base_price'] }} руб.</td>
                <td>
                    <FormNumber :form="form" :name="'tickets.' + key + '.quantity'" :quantity="true" :hide-title="true" :min="0"/>
                </td>
                <td class="pl-10 pt-15">{{ multiply(form.values['tickets.' + key + '.base_price'], form.values['tickets.' + key + '.quantity']) }} руб.</td>
            </tr>
            <tr>
                <td colspan="3" class="tickets-select-table__total text-right">Итого</td>
                <td class="tickets-select-table__total">{{ total }} руб.</td>
            </tr>
            </tbody>
        </table>
    </PopUp>
</template>

<script>
import form from "@/Core/Form";
import FormNumber from "@/Components/Form/FormNumber";
import PopUp from "@/Components/PopUp";

export default {
    components: {
        PopUp,
        FormNumber,
    },

    data: () => ({
        form: form(null, '/api/cart/terminal/add'),
        tripId: null,
        iterator: [],
    }),

    computed: {
        total() {
            let total = 0;
            this.iterator.map(key => {
                total += this.multiply(this.form.values['tickets.' + key + '.base_price'], this.form.values['tickets.' + key + '.quantity']);
            });
            return this.multiply(total, 1);
        },
        count() {
            let count = 0;
            this.iterator.map(key => {
                count += this.form.values['tickets.' + key + '.quantity'];
            });
            return count;
        }
    },

    created() {
        this.form.toaster = this.$toast;
    },

    methods: {
        multiply(a, b) {
            return Math.ceil(a * b * 100) / 100;
        },

        handle(trip) {
            this.form.reset();
            this.tripId = trip['id'];
            this.iterator = [];
            let index = 0;
            trip['rates'].map(grade => {
                this.form.set('tickets.' + index + '.grade_id', grade['id'], null);
                this.form.set('tickets.' + index + '.grade_name', grade['name'], null);
                this.form.set('tickets.' + index + '.base_price', Number(grade['value']), null);
                this.form.set('tickets.' + index + '.quantity', 0, 'integer|min:0', 'Количество');
                this.iterator.push(index);
                index++;
            })
            this.form.load();
            this.$refs.popup.show();
        },

        resolved(result) {
            if (['add', 'add_and_redirect'].indexOf(result) === -1 || this.count === 0) {
                this.$refs.popup.hide();
                return true;
            }
            if (!this.form.validate()) {
                return false;
            }
            this.$refs.popup.process(true);
            this.form.save({trip_id: this.tripId})
                .then(() => {
                    this.$store.dispatch('terminal/refresh');
                    this.$refs.popup.hide();
                    if(result === 'add_and_redirect') {
                        this.$router.push({name: 'order'});
                    }
                    return true;
                })
                .finally(() => {
                    this.$refs.popup.process(false);
                });
            return false;
        }
    }
}
</script>

<style lang="scss" scoped>
$project_font: -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica Neue, Arial, Noto Sans, sans-serif, Apple Color Emoji, Segoe UI Emoji, Segoe UI Symbol, Noto Color Emoji !default;
$base_black_color: #1e1e1e !default;

.tickets-select-table {
    font-size: 14px;
    font-family: $project_font;
    border-collapse: collapse;
    margin: 10px 0 0;

    & thead {
        color: #424242;

        & td {
            vertical-align: middle;
        }
    }

    & th {
        text-align: left;
        padding: 10px;
        cursor: default;
    }

    & td {
        vertical-align: middle;
        color: $base_black_color;
        padding: 0 10px;
        cursor: default;
        white-space: nowrap;
    }

    &__total {
        font-size: 16px;
        font-weight: bold;
        padding-top: 15px !important;
    }

    & .input-field {
        width: 140px;
    }

    &:deep(.input-number__input) {
        text-align: center;
    }

    & .input-field__errors-error {
        font-size: 12px;
    }
}
</style>
