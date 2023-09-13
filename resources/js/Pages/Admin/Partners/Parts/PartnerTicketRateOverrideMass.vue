<template>
    <FormPopUp v-if="rate" :title="'Спец. условия для партнёра'"
               :form="form"
               :options="{rate_id: rate['id'], excursion_id: rate['excursion_id'], rewrite: rewrite}"
               ref="popup"
    >
        <GuiText text-md mb-5>{{ rate['excursion'] }}</GuiText>
        <GuiText text-md>период: <b>{{ rate['start'] }} - {{ rate['end'] }}</b></GuiText>

        <table class="rate-override-table">
            <thead>
            <tr>
                <th></th>
                <th>Цена для партнёров</th>
                <th colspan="2">Комиссионное вознаграждение за продажу билетов, руб.</th>
                <th colspan="2"></th>
            </tr>
            </thead>
            <tr v-for="key in indexes">
                <td class="w-25">{{ form.values[`rates.${key}.grade_name`] }}</td>
                <td class="w-10">{{ form.values[`rates.${key}.partner_price`] }} руб.</td>
                <td class="w-20">
                    <FormDropdown :form="form" :name="`rates.${key}.partner_commission_type`" :identifier="'id'" :show="'name'"
                                  :options="[{id: 'fixed', name: 'фикс.'}, {id: 'percents', name: '% от БС'}]"
                                  :placeholder="'По умолчанию'"
                                  :has-null="true"
                                  :hide-title="true"
                                  @change="(value) => typeChanged(value, key)"
                    />
                </td>
                <td class="w-20">
                    <FormNumber :form="form" :name="`rates.${key}.partner_commission_value`"
                                :disabled="form.values[`rates.${key}.partner_commission_type`] === null"
                                :hide-title="true"
                    />
                </td>

                <template v-if="form.values[`rates.${key}.partner_commission_type`] === null">
                    <td class="w-10" v-if="form.values[`rates.${key}.commission_type`] === 'percents'">{{ form.values['rates.' + key + '.commission_value'] }}%</td>
                    <td class="w-10" v-else>фикс.</td>

                    <td class="w-10" v-if="form.values[`rates.${key}.commission_type`] === 'percents'">
                        {{ Math.floor(form.values[`rates.${key}.commission_value`] * form.values['rates.' + key + '.partner_price']) / 100 }} руб.
                    </td>
                    <td class="w-10" v-else>{{ form.values[`rates.${key}.commission_value`] }} руб.</td>
                </template>

                <template v-else>
                    <td class="w-10"></td>
                    <td class="w-15" v-if="form.values[`rates.${key}.partner_commission_type`] === 'fixed'">
                        {{ Math.floor(Number(form.values[`rates.${key}.partner_commission_value`]) * 100) / 100 }} руб.
                    </td>
                    <td class="w-10" v-else>
                        {{ Math.floor(form.values[`rates.${key}.partner_commission_value`] * form.values[`rates.${key}.partner_price`]) / 100 }} руб.
                    </td>
                </template>
            </tr>
            <tr>
                <td colspan="2"></td>
                <td colspan="2">
                    <span class="link" @click="reset">Сбросить спец. условия</span>
                    <GuiHint mt-5>Будут проставлены все значения по умолчанию</GuiHint>
                </td>
                <td colspan="2"></td>
            </tr>
            <tr>
                <td colspan="2"></td>
                <td colspan="2">
                    <InputCheckbox v-model="rewrite">Перезаписать спец. условия установленные индивидуально</InputCheckbox>
                </td>
                <td colspan="2"></td>
            </tr>
        </table>
    </FormPopUp>
</template>

<script>
import FormPopUp from "@/Components/FormPopUp";
import form from "@/Core/Form";
import GuiText from "@/Components/GUI/GuiText";
import FormDropdown from "@/Components/Form/FormDropdown";
import FormNumber from "@/Components/Form/FormNumber";
import GuiHint from "@/Components/GUI/GuiHint";
import InputCheckbox from "@/Components/Inputs/InputCheckbox.vue";

export default {
    props: {

    },

    emits: ['update'],

    components: {
        InputCheckbox,
        GuiHint,
        FormNumber,
        FormDropdown,
        GuiText,
        FormPopUp,
    },

    data: () =>     ({
        form: form('/api/rates/override/form', '/api/rates/override_mass'),
        rate: null,
        indexes: [],
        rewrite: false,
    }),

    created() {
        this.form.toaster = this.$toast;
    },

    methods: {
        show(rate) {
            this.rate = rate;
            this.form.load({rateId: rate['id']});
            this.$store.dispatch('dictionary/refresh', 'ticket_grades')
                .then(() => {
                    let indexes = [];
                    let index = 0;
                    this.$store.getters['dictionary/dictionary']('ticket_grades').map(item => {
                        let dataRate = null;
                        let commission_type = null;
                        let commission_value = null;
                        if (rate['rates'].some(rate => (rate['grade_id'] === item['id']) && (dataRate = rate))) {
                            this.form.values.filled.map(partnerRate => {
                                if (partnerRate.rate_id === dataRate['id']){
                                    commission_type = partnerRate.commission_type
                                    commission_value = partnerRate.commission_value
                                }
                            })
                            this.form.set(`rates.${index}.id`, dataRate['id'], '', '', true);
                            this.form.set(`rates.${index}.grade_id`, item['id'], '', '', true);
                            this.form.set(`rates.${index}.grade_name`, item['name'], '', '', true);
                            this.form.set(`rates.${index}.partner_price`, dataRate['partner_price'], '', '', true);
                            this.form.set(`rates.${index}.commission_type`, dataRate['commission_type'], '', '', true);
                            this.form.set(`rates.${index}.commission_value`, dataRate['commission_value'], '', '', true);
                            this.form.set(`rates.${index}.partner_commission_type`, commission_type ?? dataRate['partner_commission_type'], null, 'Тип', true);
                            this.form.set(`rates.${index}.partner_commission_value`, commission_value ?? dataRate['partner_commission_value'], 'nullable|numeric|min:0|bail', 'Комиссия', true);
                            this.form.set('rewrite', this.rewrite);
                            indexes.push(index++);
                        }
                    });
                    this.indexes = indexes;

                    this.$refs.popup.show()
                        .then(response => {
                            this.$emit('update', response.payload['rate']);
                        });
                });
        },
        typeChanged(value, key) {
            if (value === null) {
                this.form.values[`rates.${key}.partner_commission_value`] = null;
            }
        },
        reset() {
            this.indexes.map(index => {
                this.form.set(`rates.${index}.partner_commission_type`, null, null, 'Тип', true);
                this.form.set(`rates.${index}.partner_commission_value`, null, 'nullable|numeric|min:0|bail', 'Комиссия', true);
            })
        }
    }
}
</script>

<style lang="scss" scoped>
$project_font: -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica Neue, Arial, Noto Sans, sans-serif, Apple Color Emoji, Segoe UI Emoji, Segoe UI Symbol, Noto Color Emoji !default;
$base_black_color: #1e1e1e !default;

.rate-override-table {
    font-size: 14px;
    font-family: $project_font;
    border-collapse: collapse;
    margin: 10px 0 0;
    width: 750px;

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
