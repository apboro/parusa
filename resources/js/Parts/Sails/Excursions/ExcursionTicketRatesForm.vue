<template>
    <div class="inline">
        <data-field-date :class="'w-500px'" :datasource="form" :name="'start_at'" @changed="dateChanged"/>
    </div>
    <div class="inline ml-30">
        <data-field-date :class="'w-500px'" :datasource="form" :name="'end_at'" @changed="dateChanged"/>
    </div>
    <table class="rates-table mt-20">
        <thead>
        <tr>
            <td></td>
            <td class="p-10 w-140px">Базовая стоимость (БС) билетов, руб.</td>
            <td class="p-10 w-280px" colspan="2">Минимальный и максимальный диапазон стоимости билетов, руб.</td>
            <td class="p-10 w-280px" colspan="3">Комиссионное вознаграждение партнёров за продажу билетов, руб.</td>
        </tr>
        </thead>
        <tr v-for="key in form.payload['count']">
            <td class="pr-10 pt-15">{{ form.values['rates.' + key + '.grade_name'] }}</td>
            <td>
                <data-field-input :datasource="form" :name="'rates.' + key + '.base_price'" :type="'number'" @changed="(name, value) => basePriceChanged(key, value)"/>
            </td>
            <td>
                <data-field-input :datasource="form" :name="'rates.' + key + '.min_price'" :type="'number'"/>
            </td>
            <td>
                <data-field-input :datasource="form" :name="'rates.' + key + '.max_price'" :type="'number'"/>
            </td>
            <td>
                <data-field-dropdown :datasource="form" :name="'rates.' + key + '.commission_type'" :key-by="'id'" :value-by="'name'" :options="[
                            {id: 'fixed', name: 'фикс.'},
                            {id: 'percents', name: '% от БС'},
                        ]" :placeholder="'Тип'"/>
            </td>
            <td>
                <data-field-input :datasource="form" :name="'rates.' + key + '.commission_value'" :type="'number'"/>
            </td>
            <td class="pl-10 pt-15" v-if="form.values['rates.' + key + '.commission_type'] === 'fixed'">
                {{ Math.floor(Number(form.values['rates.' + key + '.commission_value']) * 100) / 100 }} руб.
            </td>
            <td class="pl-10 pt-15" v-else>{{ Math.floor(form.values['rates.' + key + '.commission_value'] * form.values['rates.' + key + '.base_price']) / 100 }} руб.</td>
        </tr>
    </table>
</template>

<script>
import DataFieldInput from "../../../Components/DataFields/DataFieldInput";
import DataFieldDropdown from "../../../Components/DataFields/DataFieldDropdown";
import DataFieldDate from "../../../Components/DataFields/DataFieldDate";

export default {
    props: {
        form: {type: Object, required: true},
    },

    components: {
        DataFieldDate,
        DataFieldDropdown,
        DataFieldInput,
    },

    methods: {
        dateChanged() {
            this.form.validate('start_at', this.form.values['start_at']);
            this.form.validate('end_at', this.form.values['end_at']);
        },

        basePriceChanged(key, value) {
            this.form.values['rates.' + key + '.max_price'] = value;
            this.form.validate('rates.' + key + '.max_price', value);
        }
    }
}
</script>
