<template>
    <div class="text text-lg">{{ form.values['title'] }}, период: {{ form.values['start_at'] }} - {{ form.values['end_at'] }}</div>
    <table class="rates-table mt-20">
        <thead>
        <tr>
            <td></td>
            <td class="p-10 w-140px">Базовая стоимость билетов</td>
            <td class="p-10 w-280px" colspan="2">Комиссионное вознаграждение за продажу билетов, руб.</td>
            <td colspan="2"></td>
        </tr>
        </thead>
        <tr v-for="key in form.payload['count']">

            <td class="pv-15 ph-10">{{ form.values['rates.' + key + '.grade_name'] }}</td>
            <td class="pv-15 ph-10 center">{{ form.values['rates.' + key + '.base_price'] }} руб.</td>
            <td>
                <data-field-dropdown :datasource="form" :name="'rates.' + key + '.partner_commission_type'" :key-by="'id'" :value-by="'name'" :options="[
                            {id: 'fixed', name: 'фикс.'},
                            {id: 'percents', name: '% от БС'},
                        ]"
                                     :placeholder="'По умолчанию'"
                                     :has-null="true"
                                     @changed="(name, value) => typeChanged(key, value)"
                />
            </td>
            <td>
                <data-field-input :datasource="form" :name="'rates.' + key + '.partner_commission_value'" :type="'number'"
                                  :disabled="form.values['rates.' + key + '.partner_commission_type'] === null"/>
            </td>

            <template v-if="form.values['rates.' + key + '.partner_commission_type'] === null">
                <td class="pv-15 ph-10" v-if="form.values['rates.' + key + '.commission_type'] === 'percents'">{{ form.values['rates.' + key + '.commission_value'] }}%</td>
                <td class="pv-15 ph-10" v-else>фикс.</td>

                <td class="pv-15 ph-10" v-if="form.values['rates.' + key + '.commission_type'] === 'percents'">
                    {{ Math.floor(form.values['rates.' + key + '.commission_value'] * form.values['rates.' + key + '.base_price']) / 100 }} руб.
                </td>
                <td class="pv-15 ph-10" v-else>{{ form.values['rates.' + key + '.commission_value'] }} руб.</td>
            </template>
            <template v-else>
                <td></td>
                <td class="pv-15 ph-10 bold" v-if="form.values['rates.' + key + '.partner_commission_type'] === 'fixed'">
                    {{ Math.floor(Number(form.values['rates.' + key + '.partner_commission_value']) * 100) / 100 }} руб.
                </td>
                <td class="pv-15 ph-10 bold" v-else>{{ Math.floor(form.values['rates.' + key + '.partner_commission_value'] * form.values['rates.' + key + '.base_price']) / 100 }}
                    руб.
                </td>
            </template>
        </tr>
    </table>
</template>

<script>
import DataFieldInput from "../../../Components/DataFields/DataFieldInput";
import DataFieldDropdown from "../../../Components/DataFields/DataFieldDropdown";

export default {
    props: {
        form: {type: Object, required: true},
    },

    components: {
        DataFieldDropdown,
        DataFieldInput,
    },

    methods: {
        typeChanged(key, value) {
            if (value === null) {
                this.form.values['rates.' + key + '.partner_commission_value'] = null;
            }
        }
    }
}
</script>
