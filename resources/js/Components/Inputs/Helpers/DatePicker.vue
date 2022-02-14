<template>
    <div class="date-picker" @click.stop.prevent="false">
        <div class="date-picker__controls">
            <span class="date-picker__controls-previous" @click="clickNavigation(-1)"><icon-backward/></span>
            <span class="date-picker__controls-display" v-if="mode==='date'">
                <span class="date-picker__controls-display-item" @click="mode = 'month'">{{ monthToLocale(this.selected.month) }}</span>
                <span class="date-picker__controls-display-item" @click="mode = 'year'">{{ selected.year }}</span>
            </span>
            <span class="date-picker__controls-display" v-if="mode==='month'">
                <span class="date-picker__controls-display-item" @click="mode = 'year'">{{ selected.year }}</span>
            </span>
            <span class="date-picker__controls-display" v-if="mode==='year'">{{ selected.years + '-' + (selected.years + 19) }}</span>
            <span class="date-picker__controls-next" @click="clickNavigation(1)"><icon-forward/></span>
        </div>

        <div class="date-picker__calendar" :class="['date-picker__calendar-'+mode]">
            <span v-for="(item, key) in getCalendar" :key="key"
                  class="date-picker__calendar-item"
                  :class="[{
                    'date-picker__calendar-item-disabled': !item['enabled'],
                    'date-picker__calendar-item-sibling': item['sibling'],
                    'date-picker__calendar-item-current': item['current'],
                    'date-picker__calendar-item-selected': item['selected'],
                  }, item['class']]"
                  @click="calendarSelect(item['value'], item['enabled'], item['month'])"
                  v-html="item['caption']"
            ></span>
        </div>

        <span class="date-picker__current">Сегодня: <span class="date-picker__current-link" @click="selectCurrent">{{ headerCurrent }}</span></span>
    </div>
</template>

<script>
import IconBackward from "@/Components/Icons/IconBackward";
import IconForward from "@/Components/Icons/IconForward";

export default {
    emits: ['selected'],

    components: {
        IconForward,
        IconBackward,
    },

    props: {
        date: {type: Object, default: null},
        from: {type: Object, default: null},
        to: {type: Object, default: null},
    },

    watch: {
        date(value) {
            this.updateSelected(value);
        }
    },

    data: () => ({
        current: {
            year: null,
            month: null,
            date: null,
        },
        selected: {
            years: null,
            year: null,
            month: null,
            date: null,
        },
        mode: 'date',
    }),

    created() {
        this.refreshCurrent();
        if (window.navigator.languages) {
            this.locale = window.navigator.languages[0];
        } else {
            this.locale = window.navigator.language;
        }
        this.updateSelected(this.date);
    },

    computed: {
        headerCurrent: function () {
            return this.current.date + ' ' + this.monthToLocale2(this.current.month) + ' ' + this.current.year;
        },

        getCalendar() {
            let list = {};
            switch (this.mode) {
                case 'year':
                    for (let i = this.selected.years; i < this.selected.years + 20; i++) {
                        list[i] = {
                            value: i,
                            caption: i,
                            enabled: (!this.from || i >= this.from.getFullYear()) && (!this.to || i <= this.to.getFullYear()),
                            sibling: false,
                            current: i === this.current.year,
                            selected: i === this.selected.year,
                            class: null,
                        };
                    }
                    break;
                case 'month':
                    for (let i = 0; i < 12; i++) {
                        list[i] = {
                            value: i,
                            caption: this.monthToLocale(i),
                            enabled: (!this.from || (this.selected.year > this.from.getFullYear() || (this.selected.year === this.from.getFullYear() && i >= this.from.getMonth())))
                                && (!this.to || (this.selected.year < this.to.getFullYear() || (this.selected.year === this.to.getFullYear() && i <= this.to.getMonth()))),
                            sibling: false,
                            current: (i === this.current.month && this.selected.year === this.current.year),
                            selected: (this.date && this.selected.year === this.date.getFullYear() && i === this.date.getMonth()),
                            class: null,
                        };
                    }
                    break;
                case 'date':
                    let {firstDayOfWeek, lastInMonth, lastInPreviousMonth} = this.getMonthParams(this.selected.year, this.selected.month);
                    let day = firstDayOfWeek === 1 ? 1 : lastInPreviousMonth - (firstDayOfWeek - 1) + 1;
                    let month = firstDayOfWeek === 1 ? 0 : -1;

                    for (let i = 0; i < 42; i++) {
                        list[i] = {
                            value: day,
                            month: month,
                            caption: day,
                            enabled: (!this.from || (this.selected.year > this.from.getFullYear() || (this.selected.year === this.from.getFullYear()
                                    && (this.selected.month + month > this.from.getMonth() || this.selected.month + month === this.from.getMonth() && day >= this.from.getDate()))))
                                && (!this.to || (this.selected.year < this.to.getFullYear() || (this.selected.year === this.to.getFullYear()
                                    && (this.selected.month + month < this.to.getMonth() || this.selected.month + month === this.to.getMonth() && day <= this.to.getDate())))),
                            sibling: month !== 0,
                            current: (month === 0 && day === this.current.date && this.selected.month === this.current.month && this.selected.year === this.current.year),
                            selected: (this.date
                                && this.selected.year === this.date.getFullYear()
                                && this.selected.month + month === this.date.getMonth()
                                && day === this.date.getDate()
                            ),
                            class: null,
                        };

                        day++;

                        if ((day > lastInPreviousMonth) && (month === -1)) {
                            day = 1;
                            month++
                        }
                        if (day > lastInMonth && month === 0) {
                            day = 1;
                            month++;
                        }
                    }
                    break;
                default:
            }
            return list;
        },
    },

    methods: {
        refreshCurrent() {
            const current = new Date();
            this.current.year = current.getFullYear();
            this.current.month = current.getMonth();
            this.current.date = current.getDate();
        },

        monthToLocale(month) {
            const date = new Date();
            date.setMonth(month);

            return date.toLocaleString(this.locale, {month: 'long'});
        },

        monthToLocale2(month) {
            const date = new Date();
            date.setMonth(month);

            return String(date.toLocaleString(this.locale, {day: '2-digit', month: 'long'})).substring(3);
        },

        updateSelected(key, value = null) {
            if (value === null) {
                if (key === null) {
                    // set current
                    let date = new Date();
                    this.selected.year = date.getFullYear();
                    this.selected.month = date.getMonth();
                    this.selected.date = date.getDate();
                } else {
                    // get from date object
                    this.selected.year = key.getFullYear();
                    this.selected.month = key.getMonth();
                    this.selected.date = key.getDate();
                }
                this.selected.years = Math.floor(this.selected.year / 20) * 20;
                return;
            }

            let dateObj = Object.assign({}, this.selected);
            dateObj[key] = value;

            const date = new Date(dateObj.year, dateObj.month, dateObj.date);
            if (date.getDate() !== dateObj.date && date.getMonth() !== dateObj.month) {
                date.setMonth(dateObj.month);
                date.setDate(0);
            }

            this.selected.year = date.getFullYear();
            this.selected.month = date.getMonth();
            this.selected.date = date.getDate();
            this.selected.years = Math.floor(this.selected.year / 20) * 20;
        },

        clickNavigation(direction) {
            switch (this.mode) {
                case 'year':
                    this.selected.years += direction * 20;
                    break;
                case 'month':
                    this.selected.year += direction;
                    break;
                case 'date':
                    this.selected.month += direction;
                    break;
                default:
            }
            this.$forceUpdate();
        },

        getMonthParams(year, month) {
            let date = new Date(year, month);
            let firstDayOfWeek = date.getDay();

            if (firstDayOfWeek === 0) firstDayOfWeek = 7;

            date.setMonth(date.getMonth() + 1);
            date.setDate(0);
            const lastInMonth = date.getDate();

            date.setDate(0);
            const lastInPreviousMonth = date.getDate();

            return {firstDayOfWeek, lastInMonth, lastInPreviousMonth};
        },

        calendarSelect(value, enabled, monthDelta) {
            if (!enabled) return;
            switch (this.mode) {
                case 'year':
                    this.selected.year = value;
                    this.mode = 'month';
                    break;
                case 'month':
                    this.selected.month = value;
                    this.mode = 'date';
                    break;
                case 'date':
                    this.selected.date = value;
                    this.selected.month += monthDelta;
                    this.$emit('selected', new Date(this.selected.year, this.selected.month, this.selected.date));
                    break;
                default:
            }
        },

        selectCurrent() {
            const date = new Date();
            if ((!this.from || this.from <= date) && (!this.to || this.to >= date)) {
                this.updateSelected('year', this.current.year);
                this.updateSelected('month', this.current.month);
                this.updateSelected('date', this.current.date);
                this.mode = 'date';
                this.$emit('selected', date);
            }
        }
    }
}
</script>

<style lang="scss">
@import "../../variables";

$project_font: -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica Neue, Arial, Noto Sans, sans-serif, Apple Color Emoji, Segoe UI Emoji, Segoe UI Symbol, Noto Color Emoji !default;
$animation_time: 150ms !default;
$animation: cubic-bezier(0.24, 0.19, 0.28, 1.29) !default;
$input_color: #1e1e1e !default;
$input_hover_color: #6fb4f7 !default;
$input_active_color: #0f82f1 !default;
$input_disabled_color: #626262 !default;
$input_background_color: #ffffff !default;
$input_error_color: #FF1E00 !default;

.date-picker {
    display: flex;
    flex-direction: column;
    width: 250px;
    background-color: $input_background_color;
    @include no_selection;

    &__controls {
        font-family: $project_font;
        font-size: 14px;
        color: $input_color;
        padding: 5px 0;
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
        font-weight: bold;

        &-previous, &-next {
            width: 35px;
            height: 35px;
            box-sizing: border-box;
            padding: 10px;
            cursor: pointer;
            transition: color $animation $animation_time;

            &:hover {
                color: $input_hover_color;
            }

            & > svg {
                width: 100%;
                height: 100%;
            }
        }

        &-display {
            cursor: default;

            &-item {
                cursor: pointer;
                margin: 0 5px;
                transition: color $animation $animation_time;

                &:hover {
                    color: $input_hover_color;
                }
            }
        }
    }

    &__calendar {
        display: flex;
        flex-wrap: wrap;

        &-date &-item:nth-child(7n):not(&-item-disabled), &-date &-item:nth-child(7n-1):not(&-item-disabled) {
            color: $input_error_color;
        }

        &-item {
            box-sizing: border-box;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: $project_font;
            font-size: 14px;
            cursor: pointer;
            color: $input_color;
            border: 1px solid transparent;
            background-color: transparent;
            border-radius: 2px;

            &-disabled {
                color: $input_disabled_color;
                cursor: default;
                opacity: 0.4;
            }

            &-sibling:not(&-disabled):not(&-selected) {
                opacity: 0.6;
            }

            &-current {
                color: $input_color;
                background-color: transparent;
                border: 1px solid $input_active_color;
            }

            &-selected {
                color: $input_background_color !important;
                background-color: $input_active_color;
                border: 1px solid $input_active_color;
            }

            &:not(&-disabled):not(&-selected):hover {
                background-color: $input_hover_color;
            }
        }

        &-date &-item {
            width: calc(100% / 7);
            height: calc(180px / 6);
        }

        &-month &-item {
            width: calc(100% / 3);
            height: calc(180px / 4);
        }

        &-year &-item {
            width: calc(100% / 5);
            height: calc(180px / 4);
        }
    }

    &__current {
        font-family: $project_font;
        font-size: 14px;
        color: $input_color;
        display: block;
        text-align: center;
        padding: 10px 0 5px;
        cursor: default;

        &-link {
            cursor: pointer;
            transition: color $animation $animation_time;
            color: $input_active_color;

            &:hover {
                color: $input_hover_color;
            }
        }
    }
}
</style>
