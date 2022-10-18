<template>
    <div>
        <div class="ap-showcase__search">
            <div class="ap-showcase__search-item">
                <span class="ap-showcase__search-item-title">Количество персон</span>
                <ShowcaseInputNumber v-model="search_parameters.persons" :min="0" :quantity="false" :original="search_parameters.persons" :placeholder="'Количество персон'">
                    <ShowcaseIconPersons/>
                </ShowcaseInputNumber>
            </div>
            <div class="ap-showcase__search-item">
                <span class="ap-showcase__search-item-title">Дата</span>
                <ShowcaseInputDate v-model="date_filter" :from="date_from" :to="date_to" :original="date_filter" ref="date">
                    <ShowcaseIconCalendar/>
                </ShowcaseInputDate>
            </div>
            <div class="ap-showcase__search-item">
                <span class="ap-showcase__search-item-title">Желаемая программа</span>
                <ShowcaseInputDropDown v-model="search_parameters.programs" :options="programs" :original="search_parameters.programs" :identifier="'id'" :show="'name'"
                                       :has-null="true"
                                       :placeholder="'Все'">
                    <ShowcaseIconCompass/>
                </ShowcaseInputDropDown>
            </div>
            <div class="ap-showcase__search-item">
                <ShowcaseButton @click="search">Подобрать рейс</ShowcaseButton>
            </div>
        </div>

        <ShowcaseLoadingProgress :loading="isLoading">
            <template v-if="trips !== null">
                <h2 class="ap-showcase__title ap-showcase__title-centered">Расписание отправлений на <span class="ap-not-brake">{{ date }}</span></h2>

                <div class="ap-showcase__results" v-if="trips !== null && trips.length > 0">
                    <table class="ap-showcase__trips">
                        <thead>
                        <tr>
                            <th class="ap-showcase__trip-time">Время отправления</th>
                            <th class="ap-showcase__trip-excursion">Причал, теплоход</th>
                            <th class="ap-showcase__trip-info">Программа</th>
                            <th class="ap-showcase__trip-duration">Время в пути</th>
                            <th class="ap-showcase__trip-price">Стоимость за взрослого</th>
                            <th class="ap-showcase__trip-status">Статус рейса</th>
                            <th class="ap-showcase__trip-buy"></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="trip in trips">
                            <td>
                                <div>
                                    {{ trip['start_time'] }}
                                </div>
                                <div style="color: #747474;">
                                    {{ trip['start_date'] }}
                                </div>
                            </td>
                            <td>
                                <span class="ap-link" @click="showPierInfo(trip)">Причал "{{ trip['pier'] }}"</span>
                                <span>{{ trip['ship'] }}</span>
                            </td>
                            <td>
                                <span class="ap-link" @click="showExcursionInfo(trip)">{{ trip['excursion'] }}</span>
                                <span>
                                        <span v-if="trip['programs'] && trip['programs'].length > 0">{{ trip['programs'].join(', ') }}</span>
                                    </span>
                            </td>
                            <td data-label="Время в пути:"><span class="ap-not-brake">{{ trip['duration'] }} мин.</span></td>
                            <td data-label="Стоимость за взрослого:"><span class="ap-not-brake">{{ trip['price'] }} руб.</span></td>
                            <td data-label="Статус рейса:"><span class="ap-not-brake">{{ trip['status'] }}</span></td>
                            <td>
                                <ShowcaseButton @click="select(trip)">Купить билеты</ShowcaseButton>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>

                <ShowcaseMessage border v-else>
                    Рейсы с заданными параметрами не найдены.
                    <div v-if="next_date" style="margin-top: 10px">
                        Ближайшая дата рейса <span class="ap-link" @click="setNextDate">{{ next_date_caption }}</span>
                    </div>
                </ShowcaseMessage>
            </template>
        </ShowcaseLoadingProgress>

        <ExcursionInfo ref="excursion"
                       :crm_url="crm_url"
                       :debug="debug"
                       :session="session"
        />
        <PierInfo ref="pier"
                  :crm_url="crm_url"
                  :debug="debug"
                  :session="session"
        />
    </div>
</template>

<script>
import ShowcaseInputDate from "@/Pages/Showcase/Components/ShowcaseInputDate";
import ShowcaseButton from "@/Pages/Showcase/Components/ShowcaseButton";
import ShowcaseInputNumber from "@/Pages/Showcase/Components/ShowcaseInputNumber";
import ShowcaseInputDropDown from "@/Pages/Showcase/Components/ShowcaseInputDropDown";
import ShowcaseLoadingProgress from "@/Pages/Showcase/Components/ShowcaseLoadingProgress";
import ShowcaseMessage from "@/Pages/Showcase/Components/ShowcaseMessage";
import ExcursionInfo from "@/Pages/Showcase/Parts/ExcursionInfo";
import PierInfo from "@/Pages/Showcase/Parts/PierInfo";
import ShowcaseIconPersons from "@/Pages/Showcase/Icons/ShowcaseIconPersons";
import ShowcaseIconCalendar from "@/Pages/Showcase/Icons/ShowcaseIconCalendar";
import ShowcaseIconCompass from "@/Pages/Showcase/Icons/ShowcaseIconCompass";

export default {
    components: {
        ShowcaseIconCompass,
        ShowcaseIconCalendar,
        ShowcaseIconPersons,
        PierInfo,
        ExcursionInfo,
        ShowcaseMessage,
        ShowcaseInputDropDown,
        ShowcaseInputNumber,
        ShowcaseButton,
        ShowcaseInputDate,
        ShowcaseLoadingProgress,
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
        }
    }),

    mounted() {
        this.search_parameters['date'] = this.lastSearch !== null && typeof this.lastSearch['date'] !== "undefined" ? this.lastSearch['date'] : null;
        this.search_parameters['persons'] = this.lastSearch !== null && typeof this.lastSearch['persons'] !== "undefined" ? this.lastSearch['persons'] : null;
        this.search_parameters['programs'] = this.lastSearch !== null && typeof this.lastSearch['programs'] !== "undefined" ? this.lastSearch['programs'] : null;
    },

    methods: {
        search() {
            if (this.search_parameters.date === null) {
                this.search_parameters.date = this.today;
            }
            this.$emit('search', this.search_parameters);
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
@import "variables";

.ap-showcase__search {
    box-sizing: border-box;
    padding: 10px 10px 20px 10px;
    border: 1px solid $showcase_light_gray_color;
    display: flex;
    flex-wrap: wrap;
}

.ap-showcase__search-item {
    display: flex;
    flex-direction: column;
    flex-grow: 0;
    padding: 10px 10px 0 10px;
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

@media screen and (min-width: 650px) {
    .ap-showcase__search-item {
        width: 50%;
    }
}

@media screen and (min-width: 920px) {
    .ap-showcase__search-item {
        width: 25%;
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
            border-bottom: none !important;
        }

        tr:not(:first-child) td:first-child {
            padding-top: 50px !important;
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
