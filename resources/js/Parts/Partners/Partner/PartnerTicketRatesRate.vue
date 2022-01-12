<template>
    <container p-20 mt-15 border>
        <heading flex items-center mb-15>
            <span class="bold">
                <activity v-if="today" :active="active"/>
                <span>{{ rate['start_at'] }} - {{ rate['end_at'] }}</span>
            </span>
            <div class="grow">
                <span v-if="overridden" class="ml-15 text-lg flex items-center"><icon-exclamation :class="'w-20px h-20px mr-10 text-orange'"/>Действуют индивидуальные условия</span>
            </div>
            <actions-menu :title="null" v-if="editable">
                <span @click="$emit('edit', rate, excursion)">Установить спец. условия</span>
<!--                <span @click="$emit('delete', rate, excursion)">Сбросить</span>-->
            </actions-menu>
        </heading>
        <table class="rates-table rates-table__decorated mt-10">
            <thead>
            <tr>
                <td class="p-10 w-20">Тип билета</td>
                <td class="p-10 w-20">Базовая стоимость билета</td>
                <td class="p-10 w-20">Минимальный и максимальный диапазон стоимости билета<sup>1</sup></td>
                <td class="p-10 w-20" colspan="2">Комиссионное вознаграждение партнёров за продажу билета<sup>2</sup></td>
                <td class="p-10 w-20" colspan="2">Специальные условия</td>
            </tr>
            </thead>
            <tr v-for="item in rates">
                <td class="pv-5 ph-10">{{ gradeName(item['grade_id']) }}</td>

                <td class="pv-5 ph-10">{{ item['base_price'] }} руб.</td>

                <td class="pv-5 ph-10">{{ item['min_price'] }} - {{ item['max_price'] }} руб.</td>

                <td class="pv-5 ph-10" :class="{'line-through': item['partner_commission_type'] !== null}" v-if="item['commission_type'] === 'percents'">{{ item['commission_value'] }}%</td>
                <td class="pv-5 ph-10" :class="{'line-through': item['partner_commission_type'] !== null}" v-else>фикс.</td>

                <td class="pv-5 ph-10" :class="{'line-through': item['partner_commission_type'] !== null}" v-if="item['commission_type'] === 'percents'">{{ Math.floor(item['commission_value'] * item['base_price']) / 100 }} руб.</td>
                <td class="pv-5 ph-10" :class="{'line-through': item['partner_commission_type'] !== null}" v-else>{{ item['commission_value'] }} руб.</td>


                <td class="pv-5 ph-10" v-if="item['partner_commission_type'] === 'percents'">{{ item['partner_commission_value'] }}%</td>
                <td class="pv-5 ph-10" v-else-if="item['partner_commission_type'] === 'fixed'">фикс.</td>
                <td class="pv-5 ph-10" v-else>—</td>

                <td class="pv-5 ph-10" v-if="item['partner_commission_type'] === 'percents'">{{ Math.floor(item['partner_commission_value'] * item['base_price']) / 100 }} руб.</td>
                <td class="pv-5 ph-10" v-else-if="item['partner_commission_type'] === 'fixed'">{{ item['partner_commission_value'] }} руб.</td>
                <td class="pv-5 ph-10" v-else>—</td>
            </tr>
        </table>
    </container>
</template>

<script>
import Container from "../../../Components/GUI/Container";
import Heading from "../../../Components/GUI/Heading";
import ActionsMenu from "../../../Components/ActionsMenu";
import Activity from "../../../Components/Activity";
import moment from "moment";
import IconExclamation from "../../../Components/Icons/IconExclamation";

export default {
    components: {IconExclamation, Activity, ActionsMenu, Heading, Container},

    emits: ['delete', 'edit'],

    props: {
        rate: {type: Object, required: true},
        excursion: {type: Object, required: true},
        today: {type: [Object, String], default: null},
        editable: {type: Boolean, default: false},
    },

    computed: {
        active() {
            let today = (typeof this.today === "string") ? moment(this.today, 'DD.MM.YYYY') : this.today;
            if (today > moment(this.rate['end_at'], 'DD.MM.YYYY')) {
                return false;
            }
            if (moment(this.rate['start_at'], 'DD.MM.YYYY') > today) {
                return true;
            }
            return true;
        },

        orders() {
            let orders = {};
            this.$store.getters['dictionary/dictionary']('ticket_grades').map(item => {
                orders[item['id']] = item['order'];
            });
            return orders;
        },

        rates() {
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
