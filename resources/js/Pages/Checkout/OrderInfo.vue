<template>
    <div>
        <div v-if="order !== null" v-for="trip in trips">
            <div class="ap-checkout__trip">

                <div class="ap-checkout__trip-title">Выбранная экскурсия</div>
                <div class="ap-checkout__trip-wrapper">
                    <div class="ap-checkout__trip-img">
                        <img :src="trip['images'][0]" :alt="trip['excursion']"/>
                    </div>
                    <div class="ap-checkout__trip-info">
                        <div class="ap-checkout__trip-info-line">
                            <span class="ap-checkout__trip-info-line-title">Экскурсия:</span>
                            <span class="ap-checkout__trip-info-line-link" @click="showExcursionInfo(trip['excursion_id'])">{{ trip['excursion'] }}</span>
                        </div>
                        <div class="ap-checkout__trip-info-line">
                            <span class="ap-checkout__trip-info-line-title">Дата отправления:</span>
                            <span class="ap-checkout__trip-info-line-text">{{ trip['start_date'] }}</span>
                        </div>
                        <div class="ap-checkout__trip-info-line">
                            <span class="ap-checkout__trip-info-line-title">Время отправления:</span>
                            <span class="ap-checkout__trip-info-line-text">{{ trip['start_time'] }}</span>
                        </div>
                        <div class="ap-checkout__trip-info-line">
                            <span class="ap-checkout__trip-info-line-title">Причал:</span>
                            <span class="ap-checkout__trip-info-line-link" @click="showPierInfo(trip['pier_id'])">{{ trip['pier'] }}</span>
                        </div>
                        <div class="ap-checkout__trip-info-line">
                            <span class="ap-checkout__trip-info-line-title">Продолжительность:</span>
                            <span class="ap-checkout__trip-info-line-text">{{ trip['duration'] }} минут</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="ap-checkout__title">Выбранные билеты</div>
            <div class="ap-checkout__tickets">
                <table class="ap-checkout__tickets-table">
                    <thead>
                    <tr>
                        <th class="ap-checkout__tickets-table-col-1">Тип билета</th>
                        <th class="ap-checkout__tickets-table-col-2">Стоимость</th>
                        <th class="ap-checkout__tickets-table-col-3">Количество</th>
                        <th class="ap-checkout__tickets-table-col-4">Сумма</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="ticket in trip['tickets']">
                        <td data-label="Тип билета:" class="ap-checkout__tickets-table-col-1">{{ ticket['grade'] }}</td>
                        <td data-label="Стоимость:" class="ap-checkout__tickets-table-col-2">{{ ticket['base_price'] }} руб.</td>
                        <td data-label="Количество:" class="ap-checkout__tickets-table-col-3">{{ ticket['quantity'] }}</td>
                        <td data-label="Сумма:" class="ap-checkout__tickets-table-col-4 ap-checkout__tickets-filled">{{ ticket['total_price'] }} руб.</td>
                    </tr>
                    <tr>
                        <td colspan="3" class="ap-checkout__tickets-table-col-total-title">Итого:</td>
                        <td class="ap-checkout__tickets-table-col-total ap-checkout__tickets-filled">{{ trip['total'] }} руб.</td>
                    </tr>
                    </tbody>
                </table>
            </div>

        </div>

        <ExcursionInfo ref="excursion"
                       :crm_url="crm_url"
                       :debug="debug"
        />
        <PierInfo ref="pier"
                  :crm_url="crm_url"
                  :debug="debug"
        />
    </div>
</template>

<script>

import PierInfo from "@/Pages/Checkout/Parts/PierInfo";
import ExcursionInfo from "@/Pages/Checkout/Parts/ExcursionInfo";

export default {
    components: {ExcursionInfo, PierInfo},

    props: {
        order: {type: Object, default: null},
        crm_url: {type: String, default: 'https://lk.excurr.ru'},
        debug: {type: Boolean, default: false},
    },

    computed: {
        trips() {
            let trips = [];
            if (typeof this.order['trips'] !== "undefined" && this.order['trips'] !== null && typeof this.order['tickets'] !== "undefined" && this.order['tickets'] !== null) {
                this.order['trips'].map(trip => {
                    let all_tickets = this.order['tickets'].filter(ticket => ticket['trip_id'] === trip['id']);
                    let tickets = {};
                    let total = 0;
                    all_tickets.map(ticket => {
                        total += ticket['base_price'];
                        if (Object.keys(tickets).indexOf(String(ticket['grade_id'])) === -1) {
                            tickets[ticket['grade_id']] = {
                                grade: ticket['grade'],
                                base_price: ticket['base_price'],
                                quantity: 1,
                                total_price: ticket['base_price']
                            };
                        } else {
                            tickets[ticket['grade_id']]['quantity']++;
                            tickets[ticket['grade_id']]['total_price'] += ticket['base_price'];
                        }
                    });
                    trips.push(Object.assign({}, trip, {tickets: tickets}, {total: total}));
                })
            }
            return trips;
        },
    },

    methods: {
        showExcursionInfo(excursion_id) {
            this.$refs.excursion.show(excursion_id);
        },

        showPierInfo(pier_id) {
            this.$refs.pier.show(pier_id);
        },
    },
}
</script>

<style lang="scss" scoped>
@import "variables";

.ap-checkout__trip {
    display: flex;
    flex-direction: column;
    box-sizing: border-box;
    padding: 20px;
    background-color: $checkout_lightest_gray_color;
    margin: 30px 0 25px;

    &-title {
        font-family: $checkout_font;
        margin: 0 0 15px;
        font-size: 20px;
        color: $checkout_link_color;
        font-weight: bold;
    }

    &-wrapper {
        display: flex;
        flex-direction: row;
        box-sizing: border-box;
    }

    &-img {
        display: block;
        width: 400px;
        max-width: 100%;
        margin-bottom: 20px;

        & > img {
            width: 100%;
        }
    }

    &-info {
        font-family: $checkout_font;
        padding: 0 0 15px 15px;
        flex-grow: 1;

        &-line {
            display: flex;
            flex-direction: row;

            &:not(:last-child) {
                margin-bottom: 20px;
            }

            &-title {
                font-size: 14px;
                width: 180px;
                flex-shrink: 0;
            }

            &-text {
                font-size: 14px;
            }

            &-link {
                font-size: 14px;
                color: $checkout_link_color;
                cursor: pointer;
                text-decoration: underline;
            }
        }
    }
}

.ap-checkout__title {
    font-family: $checkout_font;
    margin: 10px 0;
    font-size: 20px;
    color: $checkout_link_color;
    font-weight: bold;
}

.ap-checkout__tickets {
    margin: 10px 0 20px;

    &-quantity {
        width: 130px;
    }

    &-filled {
        color: $checkout_primary_color;
        font-weight: bold;
    }
}

.ap-checkout__tickets-table {
    font-family: $checkout_font;
    width: 100%;
    border-collapse: collapse;

    & thead {
        & tr {
            border-bottom: 1px solid #d7d7d7;
        }

        & th {
            text-align: left;
            font-size: 16px;
            font-weight: normal;
            padding: 10px 10px;
            color: $checkout_link_color;
        }
    }

    & tbody {
        & tr:not(:last-child) {
            border-bottom: 1px solid #d7d7d7;
        }

        & td {
            text-align: left;
            font-size: 14px;
            padding: 10px 10px;
            vertical-align: middle;
        }

        & .ap-checkout__tickets-table-col-1 {
            width: 25%;

        }

        & .ap-checkout__tickets-table-col-2 {
            width: 25%;

        }

        & .ap-checkout__tickets-table-col-3 {
        }

        & .ap-checkout__tickets-table-col-4 {
            width: 120px;
            font-size: 16px;
        }

        & .ap-checkout__tickets-table-col-total {
            height: 40px;
            font-weight: bold;
            font-size: 16px !important;

            &-title {
                height: 40px;
                font-size: 16px !important;
                text-align: right;
            }
        }
    }
}

@media screen and (max-width: 800px) {
    .ap-checkout__title {
        text-align: center;
    }
    .ap-checkout__tickets-table {
        max-width: 300px;
        margin: 0 auto;

        thead {
            border: none;
            clip: rect(0 0 0 0);
            height: 1px;
            margin: -1px;
            overflow: hidden;
            padding: 0;
            position: absolute;
            width: 1px;
        }

        tr {
            display: block;
            text-align: center;
        }

        tr:not(:first-child) {
            padding-top: 20px !important;
        }

        tr:not(:last-child) {
            padding-bottom: 15px !important;
        }

        td {
            display: block;
            padding: 5px !important;
            width: 100% !important;
            text-align: center !important;
            font-size: 16px !important;

            & > span {
                margin: 0 !important;
            }
        }

        td:before {
            content: attr(data-label);
            margin-right: 10px;
            font-weight: normal !important;
            color: $checkout_text_color !important;
        }

        & .ap-checkout__tickets-table-col-total {
            font-size: 16px !important;
            display: inline-block;
            width: auto !important;

            &-title {
                font-size: 16px !important;
                display: inline-block;
                width: auto !important;
            }
        }
    }
}

@media screen and (max-width: 850px) {
    .ap-checkout {
        &__trip {
            &-wrapper {
                flex-direction: column;
            }

            &-info {
                padding-left: 0;
            }
        }
    }
}

</style>
