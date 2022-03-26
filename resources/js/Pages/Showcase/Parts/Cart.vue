<template>
    <div>
        <h2 class="ap-showcase__title">Оформление заказа</h2>

        <h3 class="ap-showcase__title">Выбранный рейс</h3>
        <div class="ap-showcase__trip" v-if="trip !== null">
            <div class="ap-showcase__trip-wrapper">
                <div class="ap-showcase__trip-img">
                    <img :src="trip['images'][0]" :alt="trip['excursion']"/>
                </div>
                <div class="ap-showcase__trip-info">
                    <div class="ap-showcase__trip-info-line">
                        <span class="ap-showcase__trip-info-line-title">Экскурсия:</span>
                        <span class="ap-showcase__trip-info-line-text">{{ trip['excursion'] }}</span>
                    </div>
                    <div class="ap-showcase__trip-info-line">
                        <span class="ap-showcase__trip-info-line-title">Дата:</span>
                        <span class="ap-showcase__trip-info-line-text">{{ trip['start_date'] }}</span>
                    </div>
                    <div class="ap-showcase__trip-info-line">
                        <span class="ap-showcase__trip-info-line-title">Время отправления:</span>
                        <span class="ap-showcase__trip-info-line-text">{{ trip['start_time'] }}</span>
                    </div>
                    <div class="ap-showcase__trip-info-line">
                        <span class="ap-showcase__trip-info-line-title">Причал:</span>
                        <span class="ap-showcase__trip-info-line-text">{{ trip['pier'] }}</span>
                    </div>
                    <div class="ap-showcase__trip-info-line">
                        <span class="ap-showcase__trip-info-line-title">Продолжительность:</span>
                        <span class="ap-showcase__trip-info-line-text">{{ trip['duration'] }} минут</span>
                    </div>
                </div>
            </div>
        </div>

        <template v-if="has_error === false">
            <h3 class="ap-showcase__title">Билеты</h3>
            <div class="ap-showcase__tickets">
                <table v-if="trip!== null" class="ap-showcase__tickets-table">
                    <thead>
                    <tr>
                        <th class="ap-showcase__tickets-table-col-1">Тип билета</th>
                        <th class="ap-showcase__tickets-table-col-2">Стомиость</th>
                        <th class="ap-showcase__tickets-table-col-3">Количество</th>
                        <th class="ap-showcase__tickets-table-col-4">Сумма</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="rate in trip['rates']">
                        <td class="ap-showcase__tickets-table-col-1">{{ rate['name'] }}</td>
                        <td class="ap-showcase__tickets-table-col-2">{{ rate['base_price'] }} руб.</td>
                        <td class="ap-showcase__tickets-table-col-3">
                            <FormNumber :form="form" :name="'rate.' + rate['grade_id'] + '.quantity'" :hide-title="true" :quantity="true" :min="0"/>
                        </td>
                        <td class="ap-showcase__tickets-table-col-4">
                            {{ multiply(form.values['rate.' + rate['grade_id'] + '.quantity'], rate['base_price']) }} руб.
                        </td>
                    </tr>
                    <tr>
                        <td class="ap-showcase__tickets-table-col-total">Итого:</td>
                        <td></td>
                        <td></td>
                        <td class="ap-showcase__tickets-table-col-total">{{ total }}</td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <h3 class="ap-showcase__title">Контактные данные</h3>
            <div class="ap-showcase__contacts">
                <div class="ap-showcase__contacts-item">
                    <FormString :form="form" :name="'name'" :autocomplete="'given-name'"/>
                </div>
                <div class="ap-showcase__contacts-item">
                    <FormPhone :form="form" :name="'phone'" :autocomplete="'tel'"/>
                    <span class="ap-showcase__contacts-item-description">Необходим на случай отмены рейса.</span>
                </div>
                <div class="ap-showcase__contacts-item">
                    <FormString :form="form" :name="'email'" :autocomplete="'email'"/>
                    <span class="ap-showcase__contacts-item-description">На этот адрес бкдет выслан электронный билет.</span>
                </div>
            </div>

            <div class="ap-showcase__agreement">
                <InputCheckbox v-model="agreement" :small="true">
                    Я принимаю условия <a href="#">Оферты на оказание услуг</a> и даю своё <a href="#">согласие на обработку персональных данных</a>
                </InputCheckbox>
            </div>

            <div class="ap-showcase__total">
                Итого к оплате: {{ total }}
            </div>

            <div class="ap-showcase__actions">
                <GuiButton @clicked="order" :disabled="count < 1 || !agreement">Оплатить</GuiButton>
            </div>
        </template>
        <template v-else>
            <GuiMessage>{{ error_message }}</GuiMessage>
            <div class="ap-showcase__actions">
                <GuiButton @click="unselect">Подобрать другой рейс</GuiButton>
            </div>
        </template>
    </div>
</template>

<script>
import GuiButton from "@/Components/GUI/GuiButton";
import form from "@/Core/Form";
import FormNumber from "@/Components/Form/FormNumber";
import FormString from "@/Components/Form/FormString";
import FormPhone from "@/Components/Form/FormPhone";
import InputCheckbox from "@/Components/Inputs/InputCheckbox";
import GuiMessage from "@/Components/GUI/GuiMessage";

export default {
    name: "Cart",
    components: {GuiMessage, InputCheckbox, FormPhone, FormString, FormNumber, GuiButton},
    props: {
        base_url: {type: String, required: true},
        tripId: {type: Number, default: null},
        trip: {type: Object, default: null},
    },

    emits: ['select', 'order'],

    watch: {
        trip(value) {
            if (value !== null) {
                this.initForm();
            }
        }
    },

    computed: {
        total() {
            if (this.trip === null) {
                return '—';
            }
            let total = 0;
            this.trip['rates'].map(rate => {
                total += this.multiply(rate['base_price'], this.form.values['rate.' + rate['grade_id'] + '.quantity']);
            });
            return this.multiply(total, 1) + ' руб.';
        },
        count() {
            if (this.trip === null) {
                return 0;
            }
            let count = 0;
            this.trip['rates'].map(rate => {
                count += this.form.values['rate.' + rate['grade_id'] + '.quantity'];
            });
            return count;
        }
    },

    data: () => ({
        form: null,
        agreement: false,
        is_ordering: false,
        has_error: false,
        error_message: null,
    }),

    created() {
        this.form = form(null, this.base_url + '/showcase/order', {trip: this.tripId});
    },

    mounted() {
        this.$emit('select', this.tripId);
    },

    methods: {
        unselect() {
            this.$store.commit('showcase/cart', null);
            this.$emit('select', null);
        },
        initForm() {
            this.form.reset();
            this.trip['rates'].map(rate => {
                this.form.set('rate.' + rate['grade_id'] + '.quantity', 0, 'integer|min:0', 'Количество', true);
            });
            this.form.set('name', null, 'required', 'Имя', true);
            this.form.set('email', null, 'required|email|bail', 'Email', true);
            this.form.set('phone', null, 'required', 'Телефон', true);
            this.form.load();
        },
        multiply(a, b) {
            return Math.ceil(a * b * 100) / 100;
        },
        order() {
            if (!this.form.validate()) {
                return;
            }
            this.is_ordering = true;
            this.form.save()
                .then(response => {
                    // store order secret
                    const order = response.payload['order'];
                    this.$emit('order', order);

                    // redirect to payment page
                    const url = response.payload['payment_page'];

                    window.location.href = `${url}?order=${order}`;
                })
                .catch(error => {
                    this.has_error = true;
                    this.error_message = error['message'];
                })
                .finally(() => {
                    this.is_ordering = false;
                })
        }
    }
}
</script>

<style lang="scss" scoped>
$project_font: -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica Neue, Arial, Noto Sans, sans-serif, Apple Color Emoji, Segoe UI Emoji, Segoe UI Symbol, Noto Color Emoji !default;

.ap-showcase {
    &__title {
        font-family: $project_font;
        margin: 30px 0;

        h2#{&} {
            font-size: 20px;
        }

        h3#{&} {
            font-size: 16px;
        }
    }

    &__trip {
        display: flex;
        flex-direction: column;
        box-sizing: border-box;
        border: 1px solid #afafaf;
        padding: 20px;

        &-wrapper {
            display: flex;
            flex-direction: row;
            box-sizing: border-box;
        }

        &-img {
            display: block;
            width: 300px;
            margin-bottom: 20px;

            & > img {
                width: 100%;
            }
        }

        &-info {
            font-family: $project_font;
            padding: 0 0 15px 15px;

            &-line {
                display: flex;
                flex-direction: row;

                &:not(:last-child) {
                    margin-bottom: 10px;
                }

                &-title {
                    font-size: 16px;
                    width: 180px;
                    flex-shrink: 0;
                }

                &-text {
                    font-size: 16px;
                }
            }
        }
    }

    &__contacts {
        display: flex;
        flex-direction: row;
        box-sizing: border-box;
        border: 1px solid #afafaf;
        padding: 20px;

        &-item {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            padding: 0 5px;

            &-description {
                font-family: $project_font;
                padding-bottom: 5px;
                font-size: 14px;
            }
        }

        &:deep .input-field {
            flex-direction: column;
        }
    }

    &__agreement {
        padding: 15px;
    }

    &__total {
        padding: 15px;
        font-family: $project_font;
        font-size: 18px;
        text-align: right;
        background-color: #f1f1f1;
        margin-bottom: 25px;
    }

    &__actions {
        text-align: right;
    }
}


.ap-showcase__tickets-table {
    font-family: $project_font;
    width: 100%;
    border-collapse: collapse;

    & thead {
        & tr {
            border-bottom: 1px solid #afafaf;
        }

        & th {
            text-align: left;
            font-size: 14px;
            padding: 10px;
        }
    }

    & tbody {
        & tr {
            border-bottom: 1px solid #afafaf;
        }

        & td {
            text-align: left;
            font-size: 14px;
            padding: 0 10px;
            vertical-align: middle;
        }

        & .ap-showcase__tickets-table-col-3 {
            padding-top: 6px;
            width: 130px;
        }

        & .ap-showcase__tickets-table-col-4 {
            width: 120px;
        }

        & .ap-showcase__tickets-table-col-total {
            height: 60px;
            font-weight: bold;
        }
    }
}

@media screen and (max-width: 850px) {
    .ap-showcase {
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

@media screen and (max-width: 720px) {
    .ap-showcase {
        &__contacts {
            flex-direction: column;
        }
    }
}
</style>
