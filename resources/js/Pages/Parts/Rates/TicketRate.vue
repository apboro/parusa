<template>
    <GuiContainer p-20 mt-5 bm-15 border>
        <GuiHeading flex items-center mb-15>
            <div class="bold text-lg">
                <GuiActivityIndicator :active="rate['archive'] ? false : (rate['current'] ? true : null)"/>
                {{ rate['start'] }} - {{ rate['end'] }}
            </div>
            <div class="grow">
                <span v-if="overridable && overridden" class="ml-15 text-lg flex items-center"><IconExclamation :class="'w-20px h-20px mr-10 text-orange'"/>Действуют индивидуальные условия</span>
            </div>
            <slot/>
        </GuiHeading>
        <table class="rate-table">
            <thead>
            <tr>
                <th>Тип билета</th>
                <th>Базовая стоимость билета, руб.</th>
                <th v-if="withSitePrice">Цена для сайта, руб.</th>
                <th v-if="minMax">Минимальный и максимальный диапазон стоимости билета, руб.<sup v-if="hints===true">1</sup></th>
                <th colspan="2">Комиссионное вознаграждение партнёров за продажу билета, руб.<sup v-if="hints===true">2</sup></th>
                <th v-if="overridable" colspan="2">Специальные условия</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="item in rates">
                <td>{{ gradeName(item['grade_id']) }}</td>
                <td>{{ item['base_price'] }} руб.</td>
                <td v-if="withSitePrice">{{ item['site_price'] ? item['site_price'] + ' руб.' : '—' }}</td>
                <td v-if="minMax">{{ item['min_price'] }} - {{ item['max_price'] }} руб.</td>

                <td v-if="item['commission_type'] === 'percents'" :class="{'line-through': item['partner_commission_type'] !== null}">{{ item['commission_value'] }}%</td>
                <td v-else :class="{'line-through': item['partner_commission_type'] !== null}">фикс.</td>

                <td v-if="item['commission_type'] === 'percents'" :class="{'line-through': item['partner_commission_type'] !== null}">
                    {{ Math.floor(item['commission_value'] * item['base_price']) / 100 }} руб.
                </td>
                <td v-else :class="{'line-through': item['partner_commission_type'] !== null}">{{ item['commission_value'] }} руб.</td>

                <template v-if="overridable">
                    <td v-if="item['partner_commission_type'] === 'percents'">{{ item['partner_commission_value'] }}%</td>
                    <td v-else-if="item['partner_commission_type'] === 'fixed'">фикс.</td>
                    <td v-else>—</td>

                    <td v-if="item['partner_commission_type'] === 'percents'">{{ Math.floor(item['partner_commission_value'] * item['base_price']) / 100 }} руб.</td>
                    <td v-else-if="item['partner_commission_type'] === 'fixed'">{{ item['partner_commission_value'] }} руб.</td>
                    <td v-else>—</td>
                </template>
            </tr>
            </tbody>
        </table>
    </GuiContainer>
</template>

<script>
import GuiContainer from "@/Components/GUI/GuiContainer";
import GuiHeading from "@/Components/GUI/GuiHeading";
import GuiActivityIndicator from "@/Components/GUI/GuiActivityIndicator";
import IconExclamation from "@/Components/Icons/IconExclamation";

export default {
    components: {
        IconExclamation,
        GuiActivityIndicator,
        GuiHeading,
        GuiContainer
    },

    props: {
        rate: {type: Object, required: true},
        today: {type: [Object, String], default: null},
        overridable: {type: Boolean, default: false},
        hints: {type: Boolean, default: true},
        minMax: {type: Boolean, default: true},
        withSitePrice: {type: Boolean, default: false},
    },

    computed: {
        orders() {
            if (this.$store.getters['dictionary/ready']('ticket_grades') === null) {
                return null;
            }

            let orders = {};
            const dict = this.$store.getters['dictionary/dictionary']('ticket_grades');
            if (dict !== null) {
                dict.map(item => {
                    orders[item['id']] = item['order'];
                });
            }
            return orders;
        },
        rates() {
            if (this.orders === null) {
                return [];
            }
            return this.rate['rates'].sort((a, b) => {
                let ac = typeof this.orders[a['grade_id']] !== "undefined" ? this.orders[a['grade_id']] : 0;
                let bc = typeof this.orders[b['grade_id']] !== "undefined" ? this.orders[b['grade_id']] : 0;
                return ac - bc;
            });
        },
        overridden() {
            return this.rate['overridden'];
        },
    },

    created() {
        if (this.$store.getters['dictionary/ready']('ticket_grades') === null) {
            this.$store.dispatch('dictionary/refresh', 'ticket_grades');
        }
    },

    methods: {
        gradeName(id) {
            let name = null;
            const dict = this.$store.getters['dictionary/dictionary']('ticket_grades');
            if (dict !== null) {
                dict.some(item => {
                    if (item['id'] === id) {
                        name = item['name'];
                        return true;
                    }
                    return false;
                });
            }
            return name;
        },
    }
}
</script>

<style lang="scss" scoped>

$project_font: -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica Neue, Arial, Noto Sans, sans-serif, Apple Color Emoji, Segoe UI Emoji, Segoe UI Symbol, Noto Color Emoji !default;
$base_black_color: #1e1e1e !default;

$list_table_border_color: #e3e3e3 !default;
$list_table_hover_background_color: #eaf5ff !default;
$list_table_odd_background_color: #ffffff !default;
$list_table_even_background_color: #f9f8f8 !default;

.rate-table {
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
        vertical-align: top;
        color: $base_black_color;
        padding: 5px 10px;
        cursor: default;
    }

    & tr {
        border-top: 1px dashed $list_table_border_color;

        &:nth-child(odd) {
            background-color: $list_table_odd_background_color;
        }

        &:nth-child(even) {
            background-color: $list_table_even_background_color;
        }
    }

    & tbody > tr:hover {
        background-color: $list_table_hover_background_color;
    }
}
</style>
