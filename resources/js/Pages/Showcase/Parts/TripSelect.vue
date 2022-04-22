<template>
    <div>
        <div class="ap-showcase__search">
            <div class="ap-showcase__search-item">
                <span class="ap-showcase__search-item-title">Количество персон</span>
                <ShowcaseInputNumber v-model="searchPersonsProxy" :min="0" :quantity="true" :original="searchPersons" :placeholder="'Количество персон'"/>
            </div>
            <div class="ap-showcase__search-item">
                <span class="ap-showcase__search-item-title">Дата</span>
                <ShowcaseInputDate v-model="searchDateProxy" :from="date_from" :to="date_to" :original="searchDate" ref="date"/>
            </div>
            <div class="ap-showcase__search-item">
                <span class="ap-showcase__search-item-title">Желаемая программа</span>
                <ShowcaseInputDropDown v-model="searchProgramsProxy" :options="programs" :original="searchPrograms" :identifier="'id'" :show="'name'" :has-null="true"
                                       :placeholder="'Все'"/>
            </div>
            <div class="ap-showcase__search-item">
                <ShowcaseButton @click="find">Подобрать рейс</ShowcaseButton>
            </div>
        </div>

        <template v-if="isTripsLoaded">
            <h2 class="ap-showcase__title ap-showcase__title-centered">Расписание рейсов на {{ date }}</h2>

            <div class="ap-showcase__results" v-if="trips !== null && trips.length > 0">
                <table class="ap-showcase__trips">
                    <thead>
                    <tr>
                        <th class="ap-showcase__trip-time">Отправление</th>
                        <th class="ap-showcase__trip-excursion">Причал, теплоход</th>
                        <th class="ap-showcase__trip-info">Программа</th>
                        <th class="ap-showcase__trip-duration">Длительность</th>
                        <th class="ap-showcase__trip-price">Стоимость</th>
                        <th class="ap-showcase__trip-status">Статус</th>
                        <th class="ap-showcase__trip-buy"></th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="trip in trips">
                        <td data-label="Время отправления">{{ trip['start_time'] }}</td>
                        <td data-label="Причал, теплоход">
                            <span>{{ trip['pier'] }}</span>
                            <span>{{ trip['ship'] }}</span>
                        </td>
                        <td data-label="Программа">
                            <span>{{ trip['excursion'] }}</span>
                            <span>
                                        <span v-if="trip['programs'] && trip['programs'].length > 0">{{ trip['programs'].join(', ') }}</span>
                                    </span>
                        </td>
                        <td data-label="Длительность">{{ trip['duration'] }} мин.</td>
                        <td data-label="Стоимость за взрослого">{{ trip['price'] }} руб.</td>
                        <td data-label="Статус рейса">{{ trip['status'] }}</td>
                        <td>
                            <ShowcaseButton @click="select(trip)">Купить билеты</ShowcaseButton>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <ShowcaseMessage border v-else>На выбранную дату рейсы не найдены</ShowcaseMessage>

        </template>

    </div>
</template>

<script>
import PopUp from "@/Components/PopUp";
import GuiIconButton from "@/Components/GUI/GuiIconButton";
import ShowcaseInputDate from "@/Pages/Showcase/Components/ShowcaseInputDate";
import ShowcaseButton from "@/Pages/Showcase/Components/ShowcaseButton";
import ShowcaseInputNumber from "@/Pages/Showcase/Components/ShowcaseInputNumber";
import ShowcaseInputDropDown from "@/Pages/Showcase/Components/ShowcaseInputDropDown";
import ShowcaseMessage from "@/Pages/Showcase/Components/ShowcaseMessage";

export default {
    components: {
        ShowcaseMessage,
        ShowcaseInputDropDown,
        ShowcaseInputNumber,
        ShowcaseButton,
        ShowcaseInputDate,
        GuiIconButton,
        PopUp,
    },

    props: {
        partner: {type: Number, default: null},
        today: {type: String, default: null},
        date: {type: String, default: null},
        date_from: {type: String, default: null},
        date_to: {type: String, default: null},
        programs: {type: Array, default: null},
        trips: {type: Array, default: () => ([])},
        isTripsLoaded: {type: Boolean, default: false},

        searchDate: {type: String, default: null},
        searchPersons: {type: Number, default: null},
        searchPrograms: {type: [Number, Array], default: null},
    },

    emits: ['find', 'select', 'updateSearch'],

    computed: {
        searchDateProxy: {
            get() {
                return this.searchDate;
            },
            set(value) {
                this.$emit('updateSearch', 'date', value);
            }
        },
        searchPersonsProxy: {
            get() {
                return this.searchPersons;
            },
            set(value) {
                this.$emit('updateSearch', 'persons', value);
            }
        },
        searchProgramsProxy: {
            get() {
                return this.searchPrograms;
            },
            set(value) {
                this.$emit('updateSearch', 'programs', value);
            }
        },
    },

    mounted() {
        this.$emit('select', null);
    },

    methods: {
        find() {
            if (this.searchDate !== null) {
                this.$emit('find', this.search);
            }
        },
        select(trip) {
            this.$emit('select', trip['id']);
        },
    }
}
</script>

<style lang="scss" scoped>
@import "../variables";

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


.ap-showcase__title {
    font-family: $showcase_font;
    font-size: 20px;
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
            border-bottom: 1px solid #afafaf;
        }

        th {
            font-family: $showcase_font;
            text-align: left;
            font-size: 14px;
            padding: 15px 10px;
            color: $showcase_link_color;
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
            padding: 10px !important;

            &:not(:last-child) {
                border-bottom: 1px solid #e5e5e5;
            }

            &:last-child {
                text-align: right;
            }

            & > span {
                display: inline !important;
                margin: 0 !important;

                &:not(:last-child):after {
                    content: ', ';
                }
            }
        }

        td:before {
            content: attr(data-label);
            float: left;
            margin-right: 10px;
            font-weight: bold;
        }

    }
}
</style>
