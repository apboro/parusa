<template>
    <div>
        <div class="ap-showcase__heading">
            <div class="ap-showcase__heading-title">Покупка билетов</div>
            <div class="ap-showcase__heading-actions">
                <ShowcaseButton color="light" @click="unselect">Вернуться к рейсам</ShowcaseButton>
            </div>
        </div>

        <template v-if="trip !== null">
            <div class="ap-showcase__trip">
                <div class="ap-showcase__trip-title">Выбранная экскурсия</div>
                <div class="ap-showcase__trip-wrapper">
                    <div class="ap-showcase__trip-img">
                        <img :src="trip['images'][0]" :alt="trip['excursion']"/>
                    </div>
                    <div class="ap-showcase__trip-info">
                        <div class="ap-showcase__trip-info-line">
                            <span class="ap-showcase__trip-info-line-title">Экскурсия:</span>
                            <span class="ap-showcase__trip-info-line-link" @click="showExcursionInfo">{{ trip['excursion'] }}</span>
                        </div>
                        <div class="ap-showcase__trip-info-line">
                            <span class="ap-showcase__trip-info-line-title">Дата отправления:</span>
                            <span class="ap-showcase__trip-info-line-text">{{ trip['start_date'] }}</span>
                        </div>
                        <div class="ap-showcase__trip-info-line">
                            <span class="ap-showcase__trip-info-line-title">Время отправления:</span>
                            <span v-if="!trip.is_single_ticket" class="ap-showcase__trip-info-line-text">{{ trip['start_time'] }}</span>
                            <span v-if="trip.is_single_ticket" class="ap-showcase__trip-info-line-text">{{ trip['concatenated_start_at'] }}</span>
                        </div>
                        <div class="ap-showcase__trip-info-line">
                            <span class="ap-showcase__trip-info-line-title">Причал:</span>
                            <span class="ap-showcase__trip-info-line-link" @click="showPierInfo">{{ trip['pier'] }}</span>
                        </div>
                        <div class="ap-showcase__trip-info-line">
                            <span class="ap-showcase__trip-info-line-title">Продолжительность:</span>
                            <span class="ap-showcase__trip-info-line-text">{{ trip['duration'] }} минут</span>
                        </div>
                    </div>
                </div>
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

            <div class="ap-showcase__title">Билеты</div>

            <div class="ap-showcase__tickets">
                <table v-if="trip" class="ap-showcase__tickets-table">
                    <thead>
                    <tr>
                        <th class="ap-showcase__tickets-table-col-1">Тип билета</th>
                        <th class="ap-showcase__tickets-table-col-2">Стоимость</th>
                        <th class="ap-showcase__tickets-table-col-3">Количество</th>
                        <th class="ap-showcase__tickets-table-col-4">Сумма</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="rate in trip['rates']">
                        <td data-label="Тип билета:" class="ap-showcase__tickets-table-col-1">{{ rate['name'] }}
                            <span class="ap-showcase__sup-sign" v-if="rate['preferential']"><ShowcaseIconSign/></span>
                        </td>
                        <td data-label="Стоимость:" class="ap-showcase__tickets-table-col-2">{{ rate['base_price'] }} руб.</td>
                        <td class="ap-showcase__tickets-table-col-3">
                            <ShowcaseFormNumber class="ap-showcase__tickets-quantity" :form="form" :name="'rate.' + rate['grade_id'] + '.quantity'" :hide-title="true"
                                                :quantity="true" :min="0" :border="false" @change="promoCode(false)"/>
                        </td>
                        <td data-label="Сумма:" class="ap-showcase__tickets-table-col-4"
                            :class="{'ap-showcase__tickets-filled': form.values['rate.' + rate['grade_id'] + '.quantity'] > 0}">
                            {{ multiply(form.values['rate.' + rate['grade_id'] + '.quantity'], rate['base_price']) }} руб.
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3" class="ap-showcase__tickets-table-col-total-title">Итого:</td>
                        <td class="ap-showcase__tickets-table-col-total" :class="{'ap-showcase__tickets-filled': count > 0}">{{ total }}</td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="trip.reverse_excursion_id !== null">
            <ShowcaseInputCheckbox :name="choose_back_trip" v-model="checkedBackward" :label="'Выбрать обратный рейс со скидкой'" class="ap-showcase__title"/>
            <BackwardTicketSelectShowcase v-if="checkedBackward"
                                          @select-backward-trip="handleSelectBackwardTrip"
                                          :trip="this.trip" :session="this.session"/>
            </div>

            <div class="ap-showcase__title">Контактные данные</div>

            <div class="ap-showcase__text">Контактные данные необходимы на случай отмены рейса, а также для отправки билетов. Данные не передаются третьим лицам.</div>

            <div class="ap-showcase__contacts">
                <div class="ap-showcase__contacts-item">
                    <ShowcaseFormString :form="form" :name="'name'" :autocomplete="'given-name'"/>
                </div>
                <div class="ap-showcase__contacts-item">
                    <ShowcaseFormPhone :form="form" :name="'phone'" :autocomplete="'tel'"/>
                    <span class="ap-showcase__contacts-item-description">Необходим на случай отмены рейса.</span>
                </div>
                <div class="ap-showcase__contacts-item">
                    <ShowcaseFormString :form="form" :name="'email'" :autocomplete="'email'"/>
                    <span class="ap-showcase__contacts-item-description">На этот адрес будет выслан электронный билет.</span>
                </div>
            </div>

            <div class="ap-showcase__promocode">
                <span class="ap-showcase__title">Промокод</span>
                <div class="ap-showcase__promocode-input">
                    <div class="ap-showcase__promocode-item">
                        <ShowcaseFormString :form="form" :name="'promocode'" :hide-title="true" placeholder="Промокод"/>
                        <span v-if="message" class="ap-showcase__contacts-item-description">{{ message }}</span>
                    </div>
                    <div class="ap-showcase__promocode-button">
                        <ShowcaseButton @clicked="promoCode(true)" :disabled="!form.values['promocode']">Применить</ShowcaseButton>
                    </div>
                </div>
                <div class="ap-showcase__promocode-discount" v-if="status && discounted">
                    Скидка по промокоду: <span class="ap-showcase__promocode-discount-value">{{ discounted }} руб.</span>
                </div>
            </div>

            <div class="ap-showcase__agreement">
                <ShowcaseFieldWrapper :hide-title="true" :valid="agreement_valid"
                                      :errors="['Необходимо принять условия оферты на оказание услуг и дать своё согласие на обработку персональных данных']">
                    <ShowcaseInputCheckbox v-model="agree" :small="true">
                        Я принимаю условия <span class="ap-showcase__link" @click="showOfferInfo">Оферты на оказание услуг</span> и даю своё <span class="ap-showcase__link" @click="showPersonalDataInfo">согласие на обработку
                        персональных данных</span>
                    </ShowcaseInputCheckbox>
                </ShowcaseFieldWrapper>
            </div>
            <div class="ap-showcase__agreement" v-if="status">
                <ShowcaseFieldWrapper :hide-title="true" :valid="agreement_promocode_valid"
                                      :errors="['Необходимо принять условия использования промокода']">
                    <ShowcaseInputCheckbox v-model="agree_promocode" :small="true">
                        Билеты, купленные с применением промокода, возврату не подлежат
                    </ShowcaseInputCheckbox>
                </ShowcaseFieldWrapper>
            </div>

            <template v-if="has_error">
                <ShowcaseMessage>Ошибка: {{ error_message }}</ShowcaseMessage>
            </template>


            <div class="ap-showcase__checkout">
                <div class="ap-showcase__checkout-total">
                    Итого к оплате:
                    <span v-if="count > 0" class="ap-showcase__checkout-total-value">
                        <template v-if="status">
                            <s>{{ total }}</s> {{ discount_price }} руб.
                        </template>
                        <template v-else>
                            {{ total }}
                        </template>
                    </span>
                    <span v-else class="ap-showcase__checkout-total-value">В заказе отсутствуют билеты</span>
                </div>
                <div class="ap-showcase__checkout-button">
                    <ShowcaseButton @clicked="order" :disabled="count < 1">Оплатить</ShowcaseButton>
                </div>
            </div>

            <div class="ap-showcase__notice">
                <div class="ap-showcase__notice-sign">
                    <ShowcaseIconSign class="ap-showcase__notice-sign-icon"/>
                </div>
                <div class="ap-showcase__notice-text">
                    При получении билетов в кассе необходимо предоставить документ, подтверждающий право на льготу: студенческий билет, пенсионное удостоверение и т.д.
                </div>
            </div>

            <OfferInfo ref="offer"
                       :crm_url="crm_url"
                       :debug="debug"
                       :session="session"
            />
            <PersonalDataInfo ref="personal"
                              :crm_url="crm_url"
                              :debug="debug"
                              :session="session"
            />
        </template>
    </div>
</template>

<script>
import form from "@/Core/Form";
import ShowcaseButton from "@/Pages/Showcase/Components/ShowcaseButton";
import ExcursionInfo from "@/Pages/Showcase/Parts/ExcursionInfo";
import PierInfo from "@/Pages/Showcase/Parts/PierInfo";
import ShowcaseMessage from "@/Pages/Showcase/Components/ShowcaseMessage";
import ShowcaseInputNumber from "@/Pages/Showcase/Components/ShowcaseInputNumber";
import ShowcaseFormNumber from "@/Pages/Showcase/Components/ShowcaseFormNumber";
import ShowcaseFormString from "@/Pages/Showcase/Components/ShowcaseFormString";
import ShowcaseFormPhone from "@/Pages/Showcase/Components/ShowcaseFormPhone";
import ShowcaseInputCheckbox from "@/Pages/Showcase/Components/ShowcaseInputCheckbox";
import ShowcaseIconSign from "@/Pages/Showcase/Icons/ShowcaseIconSign";
import ShowcaseFieldWrapper from "@/Pages/Showcase/Components/Helpers/ShowcaseFieldWrapper";
import OfferInfo from "@/Pages/Showcase/Parts/OfferInfo.vue";
import PersonalDataInfo from "@/Pages/Showcase/Parts/PersonalDataInfo.vue";
import BackwardTicketSelectShowcase from "../../Components/BackwardTicketSelectShowcase.vue";
import InputCheckbox from "../../Components/Inputs/InputCheckbox.vue";

export default {
    components: {
        InputCheckbox,
        BackwardTicketSelectShowcase,
        PersonalDataInfo,
        OfferInfo,
        ShowcaseFieldWrapper,
        ShowcaseIconSign,
        ShowcaseInputCheckbox,
        ShowcaseFormPhone,
        ShowcaseFormString,
        ShowcaseFormNumber,
        ShowcaseInputNumber,
        PierInfo,
        ExcursionInfo,
        ShowcaseButton,
        ShowcaseMessage,
    },

    props: {
        tripId: {type: Number, default: null},
        trip: {type: Object, default: null},
        isLoading: {type: Boolean, default: false},
        crm_url: {type: String, required: true},
        debug: {type: Boolean, default: false},
        session: {type: String, required: true},
    },

    emits: ['select', 'order'],

    computed: {
        total() {
            if (this.trip === null) {
                return '—';
            }
            let total = 0;
            this.trip['rates'].map(rate => {
                total += this.multiply(rate['base_price'], this.form.values['rate.' + rate['grade_id'] + '.quantity']);
                if (this.activeBackward){
                    total += this.multiply(rate['backward_price'], this.form.values['rate.' + rate['grade_id'] + '.quantity']);
                }
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
        },
        processing() {
            return this.isLoading || this.form.is_saving;
        },
        agree: {
            get() {
                return this.agreement;
            },
            set(value) {
                this.agreement = value;
                this.agreement_valid = true;
            }
        },
        agree_promocode: {
            get() {
                return this.agreement_promocode;
            },
            set(value) {
                this.agreement_promocode = value;
                this.agreement_promocode_valid = true;
            }
        },
    },

    watch: {
        trip() {
            this.initForm();
        }
    },

    data: () => ({
        form: null,
        agreement: true,
        agreement_valid: true,
        agreement_promocode: true,
        agreement_promocode_valid: true,
        has_error: false,
        error_message: null,
        discount_price: null,
        discounted: null,
        full_price: null,
        message: null,
        status: false,
        activeBackward: false,
        checkedBackward: false,
        backwardTripId: null,
    }),

    created() {
        let url = new URL(window.location.href);
        url.searchParams.delete('ap-tid');
        this.form = form(null, this.crm_url + '/showcase/order' + (this.debug ? '?XDEBUG_SESSION_START=PHPSTORM' : ''),
            {trip: this.tripId, ref: url.toString()});
        if (this.trip) {
            this.initForm();
        }
    },

    methods: {
        handleSelectBackwardTrip($event){
          this.activeBackward = $event.activeBackward;
          this.backwardTripId = $event.backward_trip_id;
        },

        unselect() {
            this.$emit('select', null);
        },

        showPierInfo() {
            this.$refs.pier.show(this.trip['pier_id']);
        },

        showExcursionInfo() {
            this.$refs.excursion.show(this.trip['excursion_id']);
        },

        showPersonalDataInfo() {
            this.$refs.personal.show();
        },

        showOfferInfo() {
            this.$refs.offer.show();
        },

        initForm() {
            this.form.reset();
            this.trip['rates'].map(rate => {
                this.form.set('rate.' + rate['grade_id'] + '.quantity', 0, 'integer|min:0', 'Количество', true);
            });
            this.form.set('name', null, 'required', 'Имя', true);
            this.form.set('email', null, 'required|email|bail', 'Email', true);
            this.form.set('phone', null, 'required', 'Телефон', true);
            this.form.set('promocode', null, null, 'Промокод', true);
            this.form.load();
        },

        multiply(a, b) {
            return Math.ceil(a * b * 100) / 100;
        },

        order() {
            this.agreement_valid = this.agreement;
            this.agreement_promocode_valid = !this.status || this.agreement_promocode;
            if (!this.form.validate() || !this.agreement_valid || !this.agreement_promocode_valid || this.count < 1) {
                return;
            }
            this.is_ordering = true;
            // override form saving to send headers
            axios.post(this.form.save_url, {
                data: this.form.values,
                trip: this.form.options['trip'],
                ref: this.form.options['ref'],
                backwardTripId: this.backwardTripId,
            }, {headers: {'X-Ap-External-Session': this.session}})
                .then(response => {
                    // store order secret
                    const order_id = response.data.payload['order_id'];
                    const order_secret = response.data.payload['order_secret'];
                    const payment_page = response.data.payload['payment_page'];

                    localStorage.setItem('ap-showcase-order-id', order_id);
                    localStorage.setItem('ap-showcase-order-secret', order_secret);

                    // redirect to payment page
                    setTimeout(() => {
                        window.location.href = payment_page;
                    }, 100);
                })
                .catch(error => {
                    this.has_error = true;
                    this.error_message = error.response.data['message'];
                })
                .finally(() => {
                    this.is_ordering = false;
                })
        },

        promoCode(force = false) {
            if (!force && !this.status) return;

            let tickets = [];
            let tripID = this.form.options['trip'];
            this.trip['rates'].map(rate => {
                let ticket = {
                    trip_id: tripID,
                    grade_id: rate['grade_id'],
                    quantity: this.form.values['rate.' + rate['grade_id'] + '.quantity']
                }
                tickets.push(ticket);
            });

            axios.post(this.crm_url + '/showcase/promo-code/use', {promocode: this.form.values['promocode'], tickets: tickets},
                {headers: {'X-Ap-External-Session': this.session}}
            )
                .then(response => {
                    this.status = response.data.data['status'];
                    this.discount_price = this.status ? response.data.data['discount_price'] : null;
                    this.discounted = this.status ? response.data.data['discounted'] : null;
                    this.full_price = this.status ? response.data.data['full_price'] : null;
                    this.message = response.data.data['message'];
                })
                .catch(error => {
                    this.has_error = true;
                    this.error_message = error.response.data['message'];
                })
        }
    }
}
</script>

<style lang="scss" scoped>
@import "variables";

.ap-showcase__heading {
    display: flex;
    flex-direction: row;
    margin: 10px 0;
    align-items: center;

    &-title {
        font-family: $showcase_font;
        margin: 0;
        font-size: 24px;
        color: $showcase_link_color;
        flex-shrink: 0;
        flex-grow: 1;
        font-weight: bold;
    }

    &-actions {
        flex-shrink: 0;
        flex-grow: 0;
    }
}

.ap-showcase__trip {
    display: flex;
    flex-direction: column;
    box-sizing: border-box;
    padding: 20px;
    background-color: $showcase_lightest_gray_color;
    margin: 10px 0 25px;

    &-title {
        font-family: $showcase_font;
        margin: 0 0 15px;
        font-size: 20px;
        color: $showcase_link_color;
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
        font-family: $showcase_font;
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
                color: $showcase_link_color;
                cursor: pointer;
                text-decoration: underline;
            }
        }
    }
}

.ap-showcase__title {
    font-family: $showcase_font;
    margin: 10px 0;
    font-size: 24px;
    color: $showcase_link_color;
    font-weight: bold;
}

.ap-showcase__text {
    font-family: $showcase_font;
    margin: 20px 0;
    font-size: 16px;
    color: $showcase_text_color;
    font-weight: normal;
}

.ap-showcase__contacts {
    display: flex;
    flex-direction: row;
    box-sizing: border-box;

    &-item {
        flex-grow: 1;
        display: flex;
        flex-direction: column;
        padding: 0 5px;

        &-description {
            font-family: $showcase_font;
            padding-bottom: 5px;
            font-size: 14px;
        }

        &-promocode {
            flex-grow: 0.5;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 0;
        }
    }

    &:deep(.ap-input-field) {
        flex-direction: column;
    }
}

.ap-showcase__link {
    text-decoration: underline;
    color: $showcase_link_color;
    transition: color $animation $animation_time;

    &:hover {
        color: $showcase_primary_color;
    }
}

.ap-showcase__tickets {
    margin: 20px 0;

    &-quantity {
        width: 130px;
    }

    &-filled {
        color: $showcase_primary_color;
        font-weight: bold;
    }
}

.ap-showcase__agreement {
    padding: 15px 0;
}

.ap-showcase__checkout {
    display: flex;
    flex-direction: row;
    margin: 20px 0;

    &-total {
        font-family: $showcase_font;
        font-size: 16px;
        flex-grow: 1;
        background-color: #f1f1f1;
        display: flex;
        align-items: center;
        height: $showcase_size_unit;
        box-sizing: border-box;
        padding: 0 15px;

        &-value {
            margin-left: 15px;
            font-size: 24px;
            color: $showcase_primary_color;
        }
    }

    &-button {
        margin-left: 10px;
        flex-grow: 0;
    }
}

@media screen and (max-width: 600px) {
    .ap-showcase__checkout {
        background-color: #f1f1f1;
        flex-direction: column;
        padding: 10px 0;

        &-total {
            justify-content: center;
            margin-bottom: 10px;
        }

        &-button {
            width: 100%;
            margin: 0;
            text-align: center;

            & .ap-button {
                width: calc(100% - 20px);
            }
        }
    }
}

.ap-showcase__sup-sign {
    display: inline-block;
    width: 1em;
    height: 1em;

    & > svg {
        width: 100%;
        height: 100%;
    }
}

.ap-showcase__tickets-table {
    font-family: $showcase_font;
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
            color: $showcase_link_color;
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

        & .ap-showcase__tickets-table-col-1 {
            width: 25%;

        }

        & .ap-showcase__tickets-table-col-2 {
            width: 25%;

        }

        & .ap-showcase__tickets-table-col-3 {
        }

        & .ap-showcase__tickets-table-col-4 {
            width: 120px;
            font-size: 16px;
        }

        & .ap-showcase__tickets-table-col-total {
            height: 60px;
            font-weight: bold;
            font-size: 16px !important;

            &-title {
                height: 60px;
                font-size: 16px !important;
                text-align: right;
            }
        }
    }
}

.ap-showcase__notice {
    display: flex;

    &-sign {
        flex-grow: 0;
        width: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 10px;

        &-icon {
            width: 30px;
            height: 30px;
            color: $showcase_text_color;
            display: inline-block;
        }
    }

    &-text {
        flex-grow: 1;
        font-family: $showcase_font;
        margin: 20px 0;
        font-size: 16px;
        color: $showcase_text_color;
        font-weight: normal;
    }
}

.ap-showcase__promocode {
    background-color: #f1f1f1;
    padding: 16px 16px 8px 16px;
    margin-top: 16px;

    &-input {
        display: flex;
        margin: 10px 0 8px;
    }

    &-item {
        margin-right: 5px;
        max-width: 100%;
        width: 350px;
    }

    &-button {
        padding: 5px 0;
    }

    &-discount {
        font-family: $showcase_font;
        font-size: 16px;
        display: flex;
        align-items: center;
        height: $showcase_size_unit;
        box-sizing: border-box;

        &-value {
            margin-left: 15px;
            font-size: 24px;
            color: $showcase_primary_color;
        }
    }
}

@media screen and (max-width: 800px) {
    .ap-showcase__title {
        text-align: center;
    }
    .ap-showcase__tickets-table {
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
            color: $showcase_text_color !important;
        }

        & .ap-showcase__tickets-table-col-total {
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

@media screen and (max-width: 850px) {
    .ap-showcase {
        &__contacts {
            flex-direction: column;
        }
    }
}
</style>
