<template>
    <table class="rates-table mt-20">
        <thead>
        <tr>
            <td class="p-10 w-140px">Тип</td>
            <td class="p-10 w-70px">Цена</td>
            <td class="p-10 w-100px">Количество</td>
            <td class="p-10 w-100px">Стоимость</td>
        </tr>
        </thead>
        <tr v-for="key in form.payload['count']">
            <td class="pr-10 pt-15">{{ form.values['tickets.' + key + '.grade_name'] }}</td>
            <td class="pr-10 pt-15">{{ form.values['tickets.' + key + '.base_price'] }} руб.</td>
            <td>
                <data-field-input :datasource="form" :name="'tickets.' + key + '.quantity'" :type="'number'"/>
            </td>
            <td class="pl-10 pt-15">{{ form.values['tickets.' + key + '.base_price'] * form.values['tickets.' + key + '.quantity'] }} руб.</td>
        </tr>
    </table>
</template>

<script>
import DataFieldInput from "../../../Components/DataFields/DataFieldInput";

export default {
    props: {
        form: {type: Object, required: true},
    },

    components: {
        DataFieldInput,
    },

    methods: {
    }
}
</script>
