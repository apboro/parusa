<template>
    <FormPopUp :form="form"
               :title="title"
               :options="{id: rateId, excursion_id: excursionId}"
               ref="form_popup"
    >
        <div>
            <div class="inline w-500px">
                <FormDate :form="form" :name="'start_at'"/>
            </div>
            <div class="inline w-450px ml-30">
                <FormDate :form="form" :name="'end_at'" :from="form.values['start_at']"/>
            </div>
        </div>
        <table class="excursion-rate-table">
            <thead>
            <tr>
                <th class="w-15"></th>
                <th class="w-5 p-10">Базовая стоимость (БС) билетов, руб.</th>
                <th class="w-20 p-10" colspan="2">Минимальный и максимальный диапазон стоимости билетов, руб.</th>
                <th class="w-20 p-10" colspan="3">Комиссионное вознаграждение партнёров за продажу билетов, руб.</th>
                <th class="w-20 p-10" colspan="3">Цена обратного билета, руб.</th>
                <th class="w-10 p-10">Цена для сайта, руб.</th>
                <th class="w-10 p-10">Цена для партнера, руб.</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="key in iterator">
                <td>{{ form.values['rates.' + key + '.grade_name'] }}</td>
                <td>
                    <FormNumber :form="form" :name="'rates.' + key + '.base_price'"
                                :type="'number'"
                                @change="value => basePriceChanged(value, key)"
                                :hide-title="true"
                                :small="true"
                    />
                </td>
                <td>
                    <FormNumber :form="form" :name="'rates.' + key + '.min_price'" :hide-title="true" :small="true"/>
                </td>
                <td>
                    <FormNumber :form="form" :name="'rates.' + key + '.max_price'" :hide-title="true" :small="true"/>
                </td>
                <td class="w-40px">
                    <FormDropdown :form="form" :name="'rates.' + key + '.commission_type'"
                                  :identifier="'id'"
                                  :show="'name'"
                                  :hide-title="true"
                                  :small="true"
                                  :placeholder="'Тип'"
                                  :options="[
                                      {id: 'fixed', name: 'фикс.'},
                                      {id: 'percents', name: '% от БС'},
                                  ]"
                    />
                </td>
                <td class="w-40px">
                    <FormNumber :form="form" :name="'rates.' + key + '.commission_value'" :hide-title="true" :small="true"/>
                </td>
                <td class="w-40px pl-10 pt-15" v-if="form.values['rates.' + key + '.commission_type'] === 'fixed'">
                    {{ Math.floor(Number(form.values['rates.' + key + '.commission_value']) * 100) / 100 }} руб.
                </td>
                <td class="pl-10 pt-15" v-else>{{ Math.floor(form.values['rates.' + key + '.commission_value'] * form.values['rates.' + key + '.base_price']) / 100 }} руб.</td>

                <!-- backward price columns-->
                <td class="w-40px">
                    <FormDropdown :form="form" :name="'rates.' + key + '.backward_price_type'"
                                  :identifier="'id'"
                                  :show="'name'"
                                  :hide-title="true"
                                  :small="true"
                                  :placeholder="'Тип'"
                                  :options="[
                                      {id: 'fixed', name: 'фикс.'},
                                      {id: 'percents', name: '% от БС'},
                                  ]"
                    />
                </td>
                <td class="w-40px">
                    <FormNumber :form="form" :name="'rates.' + key + '.backward_price_value'" :hide-title="true" :small="true"/>
                </td>
                <td class="w-40px pl-10 pt-15" v-if="form.values['rates.' + key + '.backward_price_type'] === 'fixed'">
                    {{ Math.floor(Number(form.values['rates.' + key + '.backward_price_value']) * 100) / 100 }} руб.
                </td>
                <td class="pl-10 pt-15" v-else>{{ Math.floor(form.values['rates.' + key + '.backward_price_value'] * form.values['rates.' + key + '.base_price']) / 100 }} руб.</td>
                <!--end-->
                <td>
                    <FormNumber :form="form" :name="'rates.' + key + '.site_price'" :hide-title="true" :small="true"/>
                </td>
                <td>
                    <FormNumber :form="form" :name="'rates.' + key + '.partner_price'" :hide-title="true" :small="true"/>
                </td>
            </tr>
            </tbody>
        </table>
    </FormPopUp>
</template>

<script>
import form from "@/Core/Form";
import FormPopUp from "@/Components/FormPopUp";
import FormDate from "@/Components/Form/FormDate";
import FormNumber from "@/Components/Form/FormNumber";
import FormDropdown from "@/Components/Form/FormDropdown";

export default {
    props: {
        excursionId: {type: Number, required: true},
        providerId:  {type: Number, required: true},
    },

    components: {FormDropdown, FormNumber, FormDate, FormPopUp},

    data: () => ({
        form: form(null, '/api/rates/update'),
        rateId: 0,
        rate: {},
        iterator: [],
    }),

    computed: {
        title() {
            return this.rateId === 0 ? 'Добавление тарифа' : 'Изменение тарифа';
        },
    },

    created() {
        this.form.toaster = this.$toast;
        if (this.$store.getters['dictionary/ready']('ticket_grades') === null) {
            this.$store.dispatch('dictionary/refresh', 'ticket_grades');
        }
    },

    methods: {
        handle(rateId = 0, rate = null) {
            this.rateId = rateId;
            this.rate = rate;

            // init form
            this.form.reset();
            this.iterator = [];
            this.form.set('start_at', rate === null ? null : rate['start_at'], 'required|date|bail', 'Начало действия тарифа', true);
            this.form.set('end_at', rate === null ? null : rate['end_at'], 'required|date|after:start_at|bail', 'Окончание действия тарифа', true);

            let index = 0;

            this.$store.getters['dictionary/dictionary']('ticket_grades').filter(grade => grade.provider_id === this.providerId).map(item => {
                let grade = null;
                if (rate === null && item['enabled'] || rate !== null && rate['rates'].some(rate => (rate['grade_id'] === item['id']) && (grade = rate))) {
                    let isDefault = index > 2;

                    this.form.set('rates.' + index + '.grade_id', item['id'], null, '', true);
                    this.form.set('rates.' + index + '.grade_name', item['name'], null, '', true);
                    this.form.set('rates.' + index + '.base_price', grade === null ? (isDefault ? 0 : null) : grade['base_price'], 'required|number|min:0|bail', 'Базовая цена', true);
                    this.form.set('rates.' + index + '.site_price', grade === null ? (isDefault ? 0 : null) : grade['site_price'], 'nullable|number|min:0|bail', 'Цена для сайта', true);
                    this.form.set('rates.' + index + '.partner_price', grade === null ? (isDefault ? 0 : null) : grade['partner_price'], 'nullable|number|min:0|bail', 'Цена для партнера', true);
                    this.form.set('rates.' + index + '.min_price', grade === null ? (isDefault ? 0 : null) : grade['min_price'], 'required|number|min:0|bail', 'Минимальная', true);
                    this.form.set('rates.' + index + '.max_price', grade === null ? (isDefault ? 0 : null) : grade['max_price'], 'required|number|gte:rates.' + index + '.base_price|min:0|bail', 'Максимальная', true);
                    this.form.set('rates.' + index + '.commission_type', grade === null ? (isDefault ? 'fixed' : null) : grade['commission_type'], 'required', 'Тип', true);
                    this.form.set('rates.' + index + '.commission_value', grade === null ? (isDefault ? 0 : null) : grade['commission_value'], 'required|number|min:0|bail', 'Комиссия', true);
                    this.form.set('rates.' + index + '.backward_price_type', grade === null ? (isDefault ? 'fixed' : null) : grade['backward_price_type'], 'required', 'Тип', true);
                    this.form.set('rates.' + index + '.backward_price_value', grade === null ? (isDefault ? 0 : null) : grade['backward_price_value'], 'required|number|min:0|bail', 'Цена', true);

                    this.iterator.push(index);
                    index++;
                }
            });

            this.form.load();
            return this.$refs.form_popup.show();
        },

        basePriceChanged(value, key) {
            this.form.values['rates.' + key + '.max_price'] = value;
        },
    }
}
</script>

<style lang="scss" scoped>
$project_font: -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica Neue, Arial, Noto Sans, sans-serif, Apple Color Emoji, Segoe UI Emoji, Segoe UI Symbol, Noto Color Emoji !default;
$base_black_color: #1e1e1e !default;

.excursion-rate-table {
    font-size: 14px;
    font-family: $project_font;
    border-collapse: collapse;
    margin: 10px 0 0;
    width: 1100px;

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
    }

    & tr {

    }

    & .input-field__errors-error {
        font-size: 12px;
    }
}
</style>
