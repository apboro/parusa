<template>
    <div>
        <div class="ap-showcase__search" v-if="items !== null && items.length > 0">
            <div class="ap-showcase__search-item" :class="`ap-showcase__search-item-`+key" v-for="(date, key) in items">
                <ShowcaseV2InputCheckbox
                                        :ischecked="checked === date.date"
                                        :title="date.title"
                                        :description="date.description"
                                        :value="date.date"
                                        @search="search"
                />
            </div>
            <ShowcaseV2RadioDate
                               :from="date_from"
                               :to="date_to"
                               :dates="dates"
                               :ischecked="!items.map(i => i['date']).includes(checked)"
                               :original="date_filter"
                               @search="search"
                               ref="date">
            </ShowcaseV2RadioDate>
        </div>

        <ShowcaseV2LoadingProgress :loading="isLoading">
            <template v-if="trips !== null">
                <h2 class="ap-showcase__title ap-showcase__title-centered">Расписание отправлений на <span class="ap-not-brake">{{ date }}</span></h2>

                <div class="ap-showcase__results" v-if="trips !== null && trips.length > 0">
                    <table class="ap-showcase__trips">
                        <thead>
                        <tr>
                            <th class="ap-showcase__trip-time">Время отправления</th>
                            <th class="ap-showcase__trip-info">Программа</th>
                            <th class="ap-showcase__trip-excursion">Причал, теплоход</th>
                            <th class="ap-showcase__trip-duration">Время в пути</th>
                            <th class="ap-showcase__trip-price">Стоимость за взрослого</th>
                            <th class="ap-showcase__trip-buy"></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="trip in trips">
                            <td data-label="Время отправления:">
                                <div v-if="!trip.is_single_ticket" class="mobile-hide">
                                    {{ trip['start_time'] }}
                                </div>
                                <div v-if="!trip.is_single_ticket" class="mobile-hide" style="color: #747474;">
                                    {{ trip['start_date'] }}
                                </div>
                                <div v-if="!trip.is_single_ticket" class="mobile-show mobile-inline" style="color: #747474;">
                                      {{ trip['start_date'] }} {{ trip['start_time'] }}
                                </div>
                                <div v-if="trip.is_single_ticket" class="mobile-hide">
                                    ЕДИНЫЙ БИЛЕТ
                                </div>
                                <div v-if="trip.is_single_ticket" class="mobile-show mobile-inline" style="color: #747474;">
                                    ЕДИНЫЙ БИЛЕТ
                                </div>
                            </td>
                            <td data-label="Программа:">
                                <span class="ap-link" @click="showExcursionInfo(trip)">{{ trip['excursion'] }}</span>
                                <span>
                                    <span v-if="trip['programs'] && trip['programs'].length > 0">{{ trip['programs'].join(', ') }}</span>
                                </span>
                                <span style="color: #747474;">
                                    Осталось билетов {{ trip['tickets_left'] }}
                                </span>
                            </td>
                            <td data-label="Причал:">
                                <span class="ap-link" @click="showPierInfo(trip)">Причал "{{ trip['pier'] }}"</span>
                                <span>{{ trip['ship'] }}</span>
                            </td>
                            <td data-label="Время в пути:"><span class="ap-not-brake">{{ trip['duration'] }} мин.</span></td>
                            <td data-label="Стоимость за взрослого:"><span class="ap-not-brake">от {{ trip['price'] }} руб.</span></td>
                            <td>
                                <ShowcaseV2Button @click="select(trip)">Купить билеты</ShowcaseV2Button>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>

                <ShowcaseV2Message border v-else>
                    Рейсы с заданными параметрами не найдены.
                    <div v-if="next_date" style="margin-top: 10px">
                        Ближайшая дата рейса <span class="ap-link" @click="setNextDate">{{ next_date_caption }}</span>
                    </div>
                </ShowcaseV2Message>
            </template>
        </ShowcaseV2LoadingProgress>

        <ExcursionInfoV2 ref="excursion"
                       :crm_url="crm_url"
                       :debug="debug"
                       :session="session"
        />
        <PierInfoV2 ref="pier"
                  :crm_url="crm_url"
                  :debug="debug"
                  :session="session"
        />
    </div>
</template>

<script>
import ShowcaseV2RadioDate from "@/Pages/ShowcaseV2/Components/ShowcaseV2RadioDate";
import ShowcaseV2LoadingProgress from "@/Pages/ShowcaseV2/Components/ShowcaseV2LoadingProgress";
import ExcursionInfoV2 from "@/Pages/ShowcaseV2/Parts/ExcursionInfoV2";
import PierInfoV2 from "@/Pages/ShowcaseV2/Parts/PierInfoV2";
import ShowcaseV2InputCheckbox from "@/Pages/ShowcaseV2/Components/ShowcaseV2InputCheckbox.vue";
import ShowcaseV2Message from "@/Pages/ShowcaseV2/Components/ShowcaseV2Message.vue";
import ShowcaseV2Button from "@/Pages/ShowcaseV2/Components/ShowcaseV2Button.vue";

export default {
    components: {
        ShowcaseV2Button,
        ShowcaseV2Message,
        ShowcaseV2InputCheckbox,
        ExcursionInfoV2,
        PierInfoV2,
        ShowcaseV2RadioDate,
        ShowcaseV2LoadingProgress,
    },

    props: {
        date_from: {type: String, default: null},
        date_to: {type: String, default: null},
        programs: {type: Array, default: null},
        today: {type: String, default: null},

        date: {type: String, default: null},
        trips: {type: Array, default: null},
        next_date: {type: String, default: null},
        next_date_caption: {type: String, default: null},
        isLoading: {type: Boolean, default: false},

        dates: {type: Array, default: []},
        items: {type: Array, default: null},
        checked: {type: String, default: null},

        lastSearch: {type: Object, default: null},

        crm_url: {type: String, required: true},
        debug: {type: Boolean, default: false},
        session: {type: String, default: null},
    },

    emits: ['search', 'select'],

    computed: {
        date_filter: {
            get() {
                return this.search_parameters.date === null ? this.today : this.search_parameters.date;
            },
            set(value) {
                this.search_parameters.date = value;
            }
        }
    },

    data: () => ({
        search_parameters: {
            date: null,
            persons: null,
            programs: null,
        },
    }),

    mounted() {
        this.search_parameters['date'] = this.lastSearch !== null && typeof this.lastSearch['date'] !== "undefined" ? this.lastSearch['date'] : null;
        this.search_parameters['persons'] = this.lastSearch !== null && typeof this.lastSearch['persons'] !== "undefined" ? this.lastSearch['persons'] : null;
        this.search_parameters['programs'] = this.lastSearch !== null && typeof this.lastSearch['programs'] !== "undefined" ? this.lastSearch['programs'] : null;
    },

    methods: {
        search(value) {
            this.$emit('search', value);
        },

        select(trip) {
            this.$emit('select', trip['id']);
        },

        showPierInfo(trip) {
            this.$refs.pier.show(trip['pier_id']);
        },

        showExcursionInfo(trip) {
            this.$refs.excursion.show(trip['excursion_id']);
        },

        setNextDate() {
            this.search_parameters.date = this.next_date;
            this.$emit('search', this.search_parameters);
        },
    }
}
</script>

<style lang="scss" scoped>
@import "/resources/js/Pages/Showcase/variables";

.ap-showcase__search {
    box-sizing: border-box;
    padding: 17px 10px;
    border: 1px solid $showcase_light_gray_color;
    display: flex;
    flex-wrap: wrap;
}

.ap-showcase__search-item {
    display: flex;
    flex-direction: column;
    flex-grow: 0;
    padding: 0 5px;
    border-radius: 2px;
    box-sizing: border-box;
    justify-content: flex-end;
    width: 100%;
    min-height: 10px;

    &-title {
        height: 26px;
        font-family: $showcase_font;
        font-size: 16px;
        box-sizing: border-box;
        padding: 5px;
    }
}

.mobile-show {
    display: none;
}

@media screen and (max-width: 769px) {
    .ap-showcase__search-item {
        width: 33.33%;
    }

    .ap-showcase__search-item-3 {
        display: none;
    }

    .mobile {
        &-show {
            display: inline-block;
        }

        &-hide {
            display: none;
        }

        &-inline {
            display: inline-block;
            color: #747474;
        }
    }
}

@media screen and (min-width: 770px) {
    .ap-showcase__search-item {
        width: 20%;
    }
}

.ap-link {
    color: $showcase_link_color;
    text-decoration: underline;
    cursor: pointer;
}

.ap-not-brake {
    white-space: nowrap;
}

.ap-showcase__title {
    font-family: $showcase_font;
    font-size: 24px;
    margin: 30px 0;
    color: $showcase_link_color;

    &-centered {
        text-align: center;
    }
}

.ap-showcase__trips {
    border-collapse: collapse;
    width: 100%;

    thead {
        tr {
            border-bottom: 1px solid #d7d7d7;
        }

        th {
            font-family: $showcase_font;
            text-align: left;
            font-size: 16px;
            padding: 15px 10px;
            color: $showcase_link_color;
            font-weight: normal;
        }
    }

    tbody {
        tr {
            border-bottom: 1px solid #d7d7d7;
        }

        td {
            font-family: $showcase_font;
            text-align: left;
            font-size: 14px;
            padding: 15px 10px;

            & > span {
                display: block;

                &:not(:last-child) {
                    margin-bottom: 5px;
                }
            }
        }
    }
}

@media screen and (max-width: 1000px) {

    .ap-showcase__trips {
        max-width: 500px;
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
            padding-bottom: 15px;
            display: block;
        }

        tr:not(:first-child) td:first-child {
            padding-top: 20px !important;
        }

        tr:last-child {
            border-bottom: none !important;
        }

        td {
            display: block;
            text-align: right;
            padding: 5px !important;

            & > span {
                margin: 0 !important;
            }
        }

        td:before {
            content: attr(data-label);
            float: left;
            margin-right: 10px;
        }
    }
}
</style>
