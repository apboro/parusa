<template>
    <GuiContainer p-20 mt-15 bm-15 border>
        <GuiHeading flex items-center mb-15>
            <GuiHeading bold grow>
                <GuiActivityIndicator :active="rate['archive'] ? false : (rate['current'] ? true : null)"/>
                {{ rate['start'] }} - {{ rate['end'] }}
            </GuiHeading>
            <slot/>
        </GuiHeading>
        <table class="rate-table">
            <thead>
            <tr>
                <th>Тип билета</th>
                <th>Базовая стоимость билета, руб.</th>
                <th>Минимальный и максимальный диапазон стоимости билета, руб.<sup>1</sup></th>
                <th colspan="2">Комиссионное вознаграждение партнёров за продажу билета, руб.<sup>2</sup></th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="item in rates">
                <td>{{ gradeName(item['grade_id']) }}</td>
                <td>{{ item['base_price'] }} руб.</td>
                <td>{{ item['min_price'] }} - {{ item['max_price'] }} руб.</td>
                <td v-if="item['commission_type'] === 'percents'">{{ item['commission_value'] }}%</td>
                <td v-else>фикс.</td>
                <td v-if="item['commission_type'] === 'percents'">{{ Math.floor(item['commission_value'] * item['base_price']) / 100 }} руб.</td>
                <td v-else>{{ item['commission_value'] }} руб.</td>
            </tr>
            </tbody>
        </table>
    </GuiContainer>
</template>

<script>
import GuiContainer from "@/Components/GUI/GuiContainer";
import GuiHeading from "@/Components/GUI/GuiHeading";
import GuiActivityIndicator from "@/Components/GUI/GuiActivityIndicator";

export default {
    components: {
        GuiActivityIndicator,
        GuiHeading,
        GuiContainer

    },

    props: {
        rate: {type: Object, required: true},
        today: {type: [Object, String], default: null},
    },

    computed: {
        orders() {
            if (!this.$store.getters['dictionary/ready']('ticket_grades')) {
                return null;
            }

            let orders = {};
            this.$store.getters['dictionary/dictionary']('ticket_grades').map(item => {
                orders[item['id']] = item['order'];
            });
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
    },

    created() {
        if (this.$store.getters['dictionary/ready']('ticket_grades') === null) {
            this.$store.dispatch('dictionary/refresh', 'ticket_grades');
        }
    },

    methods: {
        gradeName(id) {
            let name = null;
            this.$store.getters['dictionary/dictionary']('ticket_grades').some(item => {
                if (item['id'] === id) {
                    name = item['name'];
                    return true;
                }
                return false;
            });
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
