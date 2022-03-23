<template>
    <div>
        <div class="ap-showcase__search">
            <div class="ap-showcase__search-item ap-showcase__search-item-1">
                <span class="ap-showcase__search-item-title">Дата</span>
                <div class="ap-showcase__search-item-wrapper">
                    <GuiIconButton :border="false" @click="setDay(-1)">
                        <IconBackward/>
                    </GuiIconButton>
                    <InputDate v-model="searchDateProxy" :from="date_from" :to="date_to" :original="searchDate" ref="date"/>
                    <GuiIconButton :border="false" @click="setDay(1)">
                        <IconForward/>
                    </GuiIconButton>
                </div>
            </div>
            <div class="ap-showcase__search-item ap-showcase__search-item-2">
                <span class="ap-showcase__search-item-title">Количество персон</span>
                <InputNumber v-model="searchPersonsProxy" :min="0" :quantity="true" :original="searchPersons"/>
            </div>
            <div class="ap-showcase__search-item ap-showcase__search-item-3">
                <span class="ap-showcase__search-item-title">Желаемая программа</span>
                <InputDropDown v-model="searchProgramsProxy" :options="programs" :original="searchPrograms" :identifier="'id'" :show="'name'" :has-null="true"
                               :placeholder="'Все'"/>
            </div>
            <div class="ap-showcase__search-item ap-showcase__search-item-4">
                <GuiButton @click="find">Подобрать рейс</GuiButton>
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
                            <GuiButton @click="select(trip)">Купить билеты</GuiButton>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <GuiMessage v-else>На выбранную дату рейсы не найдены</GuiMessage>

        </template>
    </div>
</template>

<script>
import InputDate from "@/Components/Inputs/InputDate";
import InputNumber from "@/Components/Inputs/InputNumber";
import InputDropDown from "@/Components/Inputs/InputDropDown";
import GuiButton from "@/Components/GUI/GuiButton";
import GuiMessage from "@/Components/GUI/GuiMessage";
import PopUp from "@/Components/PopUp";
import GuiIconButton from "@/Components/GUI/GuiIconButton";
import IconBackward from "@/Components/Icons/IconBackward";
import IconForward from "@/Components/Icons/IconForward";

export default {
    components: {IconForward, IconBackward, GuiIconButton, PopUp, GuiMessage, GuiButton, InputDropDown, InputNumber, InputDate},

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
        setDay(direction) {
            this.$refs.date.addDays(direction);
        },
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
$project_font: -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica Neue, Arial, Noto Sans, sans-serif, Apple Color Emoji, Segoe UI Emoji, Segoe UI Symbol, Noto Color Emoji !default;

.ap-showcase {
    &__search {
        box-sizing: border-box;
        padding: 10px 10px 20px 10px;
        border: 1px solid #afafaf;
        display: flex;
        flex-wrap: wrap;

        &-item {
            display: flex;
            flex-direction: column;
            flex-grow: 1;
            padding: 10px 10px 0 10px;
            justify-content: flex-end;

            &-wrapper {
                display: flex;
                flex-direction: row;
            }

            &-1 {
                min-width: 200px;
            }

            &-2 {
                min-width: 200px;
            }

            &-3 {
                min-width: 300px;
            }

            &-4 {
                padding-top: 20px;
                min-width: 200px;
            }

            &-title {
                height: 26px;
                font-family: $project_font;
                font-size: 14px;
                box-sizing: border-box;
                padding: 5px;
            }
        }
    }

    &__title {
        font-family: $project_font;
        font-size: 20px;
        margin: 30px 0;

        &-centered {
            text-align: center;
        }
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
            font-family: $project_font;
            text-align: left;
            font-size: 14px;
            padding: 15px 10px;
        }
    }

    tbody {
        tr {
            border-bottom: 1px solid #d7d7d7;
        }

        td {
            font-family: $project_font;
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
