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


                <div class="ap-showcase__results" v-if="trips.length > 0">
                    <div class="ap-showcase__select">
                        <span class="ap-showcase__select-title">{{ excursions.length > 1 ? 'Выберите экскурсию' : 'Экскурсия' }}</span>
                        <ShowcaseInputDropDown v-model="selected_excursion_id"
                                               :options="excursions"
                                               :identifier="'id'"
                                               :show="'name'"
                                               :disabled="excursions.length === 1"
                                               :has-null="false"
                                               @change="handleExcursionChange"/>
                    </div>


                    <div v-if="selected_excursion_id" class="ap-showcase__select">
                        <span class="ap-showcase__select-title">{{tripPiers.length > 1 ? 'Выберите причал' : 'Причал' }}</span>
                        <ShowcaseInputDropDown v-model="selected_pier_id"
                                               :options="tripPiers"
                                               :identifier="'id'"
                                               :show="'name'"
                                               :has-null="false"
                                               :placeholder="'Все'"
                                               :disabled="tripPiers.length === 1"
                                               @change="handlePierChange"/>
                    </div>

                    <!--                simple trip-->
                    <div v-if="showcase3Store.trip && !showcase3Store.trip.trip_with_seats" style="display: flex;"
                         class="ap-showcase__time-wrapper">

                        <div v-if="showcase3Store.trip.is_single_ticket" class="ap-showcase__time">
                            <span class="ap-showcase__time_edin">БИЛЕТ НА ВЕСЬ ДЕНЬ</span>
                        </div>
                        <div v-else class="ap-showcase__time">
                            <p>Выберите время:</p>
                            <template v-for="trip in trips">
                                <ShowcaseV3TimeButton
                                    v-if="trip.pier_id === selected_pier_id"
                                    :color="showcase3Store.trip.id === trip.id ? 'purple' : 'white'"
                                    @click="selectTrip(trip)">
                                    {{ trip['start_time'] }}
                                </ShowcaseV3TimeButton>
                            </template>
                        </div>
                        <TicketsSelectV3 v-if="showcase3Store.trip"
                                         :crm_url="crm_url"
                                         :session="session"
                                         @changeTickets="handleTicketsChange"/>

                        <TripInfo :trip="showcase3Store.trip"/>
                    </div>

                    <!--                scheme trip-->
                    <div v-else-if="showcase3Store.trip">
                        <div style="display: flex;">
                            <div class="ap-showcase__select">
                                <span class="ap-showcase__select-title">Выберите время рейса: </span>
                                <div v-for="trip in trips">
                                    <ShowcaseV3TimeButton
                                        :color="showcase3Store.trip.id === trip.id ? 'purple' : 'white'"
                                        @click="selectTripWithScheme(trip)">
                                        {{ trip['start_time'] }}
                                    </ShowcaseV3TimeButton>
                                </div>
                            </div>
                        </div>
                        <DynamicSchemeContainer
                            :data="this.showcase3Store.trip"
                            :shipId="this.showcase3Store.trip['shipId']"
                            :scheme_name="this.showcase3Store.trip['scheme_name']"
                            :selecting="true"
                            @selectSeat="handleSelectSeat"/>

                        <SelectedTickets v-if="this.showcase3Store.tickets.length > 0"
                                         :tickets="this.showcase3Store.tickets"/>
                    </div>
                </div>


                <ShowcaseV2Message border v-else>
                    Рейсы с заданными параметрами не найдены.
                    <div v-if="next_date" style="margin-top: 10px">
                        Ближайшая дата рейса <span class="ap-link" @click="setNextDate">{{ next_date_caption }}</span>
                    </div>
                </ShowcaseV2Message>

                <div v-if="showcase3Store.trip && showcase3Store.trip?.reverse_excursion_id !== null">
                    <ShowcaseInputCheckbox :name="'choose_back_trip'"
                                           v-model="checkedBackward"
                                           :label="'Выбрать обратный рейс со скидкой'"
                                           :big="true"/>
                    <div style="text-align: center">
                        <BackwardTicketSelectShowcase3 v-if="checkedBackward"
                                                       :trip="this.showcase3Store.trip"
                                                       :session="session"
                                                       :crm_url="crm_url"/>
                    </div>
                </div>

                <div class="ap-showcase__contacts-wrapper">
                    <div class="ap-showcase__contacts-title">Укажите ваши контактные данные</div>

                    <div class="ap-showcase__contacts-text">Контактные данные необходимы на случай отмены рейса, а также
                        <br> для отправки
                        билетов. Данные не передаются третьим лицам.
                    </div>

                    <div class="ap-showcase__contacts-inner">
                        <ContactInfo/>
                        <!--                    <img :src="excursions[0]['excursion_first_image_url']" alt="excursion_image">-->

                        <div class="ap-showcase__total-promocode">
                            <Promocode v-if="showcase3Store.trip && !showcase3Store.trip.trip_with_seats"
                                       @use="promoCode(true)" :message="this.message"/>
                            <PromocodeAgreement/>
                        </div>

                        <template v-if="has_error">
                            <ShowcaseV2Message>Ошибка: {{ error_message }}</ShowcaseV2Message>
                        </template>

                        <div class="ap-showcase__total-pay">
                            <TotalToPay v-if="showcase3Store.trip" @pay="order"/>

                            <Agreement
                                :crm_url="crm_url"
                                :debug="debug"
                                :session="session"
                            />
                        </div>

                        <CommentToPayer/>
                    </div>
                </div>


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

        <ShowcaseV2PopUp ref="category"
                         :buttons="[
               {result: 'ok', caption: 'OK', color: 'green', disabled: !selectedGrade || (selectedGrade.menus.length > 0 ? !selectedMenu : false)},
           ]">

            <div v-for="(grade, index) in seatGrades" :key="index">
                <label v-if="getGradePrice(grade.grade.id)">
                    <input @click="selectedMenu = null" style="margin-top: 10px" type="radio"
                           v-model="selectedGrade"
                           :value="grade.grade"
                           :name="'grade-select'"> {{ grade.grade.name }} - {{ getGradePrice(grade.grade.id) }} руб.
                </label>
            </div>
            <div style="display: flex; flex-direction: column; align-items: center"
                 v-if="selectedGrade && selectedGrade.menus.length > 0">
                <span style="margin-top: 20px;">Выберите меню</span>
                <label v-for="(menu, index) in selectedGrade.menus" :key="index">
                    <input style="margin-top: 10px;" type="radio" v-model="selectedMenu" :value="menu"
                           :name="'menu-select'"> {{ menu.name }}
                </label>
            </div>
        </ShowcaseV2PopUp>

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
import ShowcaseInputDropDown from "@/Pages/Showcase/Components/ShowcaseInputDropDown.vue";
import TicketsSelectV3 from "@/Pages/ShowcaseV3/TicketsSelectV3.vue";
import PersonalDataInfo from "@/Pages/Showcase/Parts/PersonalDataInfo.vue";
import ShowcaseIconSign from "@/Pages/Showcase/Icons/ShowcaseIconSign.vue";
import ShowcaseFieldWrapper from "@/Pages/Showcase/Components/Helpers/ShowcaseFieldWrapper.vue";
import ShowcaseButton from "@/Pages/Showcase/Components/ShowcaseButton.vue";
import ShowcaseFormString from "@/Pages/Showcase/Components/ShowcaseFormString.vue";
import OfferInfo from "@/Pages/Showcase/Parts/OfferInfo.vue";
import ShowcaseFormPhone from "@/Pages/Showcase/Components/ShowcaseFormPhone.vue";
import ShowcaseInputCheckbox from "@/Pages/Showcase/Components/ShowcaseInputCheckbox.vue";
import ShowcaseInputString from "@/Pages/Showcase/Components/ShowcaseInputString.vue";
import ShowcaseInputPhone from "@/Pages/Showcase/Components/ShowcaseInputPhone.vue";
import ContactInfo from "@/Pages/ShowcaseV3/Parts/ContactInfo.vue";
import Promocode from "@/Pages/ShowcaseV3/Parts/Promocode.vue";
import Agreement from "@/Pages/ShowcaseV3/Parts/Aggreement.vue";
import PromocodeAgreement from "@/Pages/ShowcaseV3/Parts/PromoceodeAggreement.vue";
import TotalToPay from "@/Pages/ShowcaseV3/Parts/TotalToPay.vue";
import CommentToPayer from "@/Pages/ShowcaseV3/Parts/CommentToPayer.vue";
import TripInfo from "@/Pages/ShowcaseV3/Parts/TripInfo.vue";
import ShowcaseV3TimeButton from "@/Pages/ShowcaseV3/Components/ShowcaseV3TimeButton.vue";
import form from "@/Core/Form";
import {useShowcase3Store} from "@/Stores/showcase3-store";
import {mapStores} from "pinia";
import BackwardTicketSelectShowcase3 from "@/Components/BackwardTicketSelectShowcase3.vue";
import ShowcaseV2PopUp from "@/Pages/ShowcaseV2/Components/ShowcaseV2PopUp.vue";
import seatMethods from "@/Mixins/seatMethods.vue";
import SelectedTickets from "@/Pages/Parts/Seats/SelectedTickets.vue";
import DynamicSchemeContainer from "@/Pages/Admin/Ships/SeatsSchemes/DynamicSchemeContainer.vue";

export default {
    components: {
        DynamicSchemeContainer, SelectedTickets,
        ShowcaseV2PopUp,
        BackwardTicketSelectShowcase3,
        TripInfo,
        CommentToPayer,
        TotalToPay,
        PromocodeAgreement,
        Agreement,
        Promocode,
        ContactInfo,
        ShowcaseInputPhone,
        ShowcaseInputString,
        ShowcaseInputCheckbox,
        ShowcaseFormPhone,
        OfferInfo,
        ShowcaseFormString,
        ShowcaseButton,
        ShowcaseFieldWrapper,
        ShowcaseIconSign,
        PersonalDataInfo,
        TicketsSelectV3,
        ShowcaseInputDropDown,
        ShowcaseV2Button,
        ShowcaseV2Message,
        ShowcaseV2InputCheckbox,
        ExcursionInfoV2,
        PierInfoV2,
        ShowcaseV2RadioDate,
        ShowcaseV2LoadingProgress,
        ShowcaseV3TimeButton
    },

    props: {
        date_from: {type: String, default: null},
        date_to: {type: String, default: null},
        programs: {type: Array, default: null},
        today: {type: String, default: null},
        excursions: {type: Array, default: []},

        date: {type: String, default: null},
        trips: {type: Object, default: null},
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

    mixins: [seatMethods],
    emits: ['search', 'select_trip', 'select_excursion'],

    computed: {
        date_filter: {
            get() {
                return this.search_parameters.date === null ? this.today : this.search_parameters.date;
            },
            set(value) {
                this.search_parameters.date = value;
            }
        },
        tripPiers() {
            return this.trips.map(trip => ({
                name: trip.pier,
                id: trip.pier_id
            })).filter((value, index, self) =>
                    index === self.findIndex((t) => (
                        t.id === value.id
                    ))
            );
        },
        ...mapStores(useShowcase3Store),

    },
    watch: {
        checkedBackward(value) {
            if (value === false) {
                this.activeBackward = false;
                this.showcase3Store.backwardTrip = null;
            }
        },
        trips(newVal) {
            if (newVal?.length > 0) {
                this.selected_pier_id = newVal[0].pier_id;
            }
        },
    },

    data: () => ({
        form: null,
        agree: null,
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
        count: null,
        activeBackward: false,
        checkedBackward: true,
        backwardTripId: null,
        selectedSeats: [],
        seatGrades: null,
        selectedMenu: null,
        selectedGrade: null,
        tickets: [],
        selected_excursion_id: null,
        selected_pier_id: null,
        selected_trip: null,
        init_trip: null,
        search_parameters: {
            date: null,
            persons: null,
            programs: null,
        },
    }),
    created() {
        let url = new URL(window.location.href);
        url.searchParams.delete('ap-tid');
        this.form = form(null, this.crm_url + '/showcase/order' + (this.debug ? '?XDEBUG_SESSION_START=PHPSTORM' : ''),
            {ref: url.toString()});
        this.form.set('promocode', null);
    },

    mounted() {
        this.search_parameters['date'] = this.lastSearch !== null && typeof this.lastSearch['date'] !== "undefined" ? this.lastSearch['date'] : null;
        this.search_parameters['persons'] = this.lastSearch !== null && typeof this.lastSearch['persons'] !== "undefined" ? this.lastSearch['persons'] : null;
        this.search_parameters['programs'] = this.lastSearch !== null && typeof this.lastSearch['programs'] !== "undefined" ? this.lastSearch['programs'] : null;
        this.selected_excursion_id = this.excursions[0].id;
    },

    methods: {
        getGradePrice(gradeId) {
            return this.showcase3Store.trip['rates'].find(e => e.grade_id === gradeId)?.base_price;
        },
        getFilteredGrades(categoryId) {
            return this.showcase3Store.trip['seat_tickets_grades'].filter(el => el.seat_category_id === categoryId)
        },
        handleSelectSeat(data) {
            if (!data.deselect) {
                let categoryId = this.showcase3Store.trip['seats'].find(el => el.seat_id === data.seatId).category.id;
                this.seatGrades = this.getFilteredGrades(categoryId);

                this.$refs.category.show().then(() => {
                    this.showcase3Store.tickets.push({
                        seatId: data.seatId,
                        seatNumber: data.seatNumber,
                        menu: this.selectedMenu,
                        grade: this.selectedGrade,
                        price: this.getGradePrice(this.selectedGrade.id)
                    })
                })
            } else {
                this.showcase3Store.tickets = this.showcase3Store.tickets.filter(ticket => ticket.seatId !== data.seatId);
            }
            this.selectedSeats = data.selectedSeats;
        },
        handlePierChange() {
            this.showcase3Store.ticketsData = [];
            this.showcase3Store.tickets = [];
            this.selected_trip = null;
        },
        handleExcursionChange() {
            this.selected_trip = null;
            this.showcase3Store.trip = null;
            this.showcase3Store.ticketsData = [];
            this.showcase3Store.tickets = [];
            this.showcase3Store.excursion = this.selected_excursion_id;
            this.selected_pier_id = null;
            this.$emit('select_excursion', this.selected_excursion_id)
        },
        handleTicketsChange(values) {
            for (const key of Object.keys(values)) {
                this.form.set(key, values[key])
            }
            this.count = Object.keys.length;
            if (this.showcase3Store.promocode && this.showcase3Store.promocode.length > 0) {
                this.promoCode(true);
            }
        },

        search(value) {
            this.$emit('search', value);
        },

        selectTrip(trip) {
            this.showcase3Store.trip = trip
            this.selected_trip = trip;
        },
        selectTripWithScheme(trip) {
            this.showcase3Store.trip = trip
            this.selected_trip = trip;
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
        promoCode(force = false) {

            if (!force && !this.status) return;

            let tickets = [];
            let tripID = this.showcase3Store.trip.id;
            this.showcase3Store.trip.rates.map(rate => {
                let ticket = {
                    trip_id: tripID,
                    grade_id: rate['grade_id'],
                    quantity: this.form.values['rate.' + rate['grade_id'] + '.quantity']
                }
                tickets.push(ticket);
            });

            axios.post(this.crm_url + '/showcase_v2/promo-code/use', {
                    promocode: this.showcase3Store.promocode,
                    tickets: tickets
                },
                {headers: {'X-Ap-External-Session': this.session}}
            )
                .then(response => {
                    this.status = response.data.data['status'];
                    this.showcase3Store.discount.status = response.data.data['status'];
                    this.showcase3Store.discount.discount_price = this.status ? response.data.data['discount_price'] : null;
                    this.showcase3Store.discount.discounted = this.status ? response.data.data['discounted'] : null;
                    this.showcase3Store.discount.full_price = this.status ? response.data.data['full_price'] : null;
                    this.message = response.data.data['message'];
                })
                .catch(error => {
                    this.has_error = true;
                    this.error_message = error.response.data['message'];
                })
        },
        order() {
            if (this.showcase3Store.trip.trip_with_seats) {
                this.orderWithScheme()
            }
            this.agreement_valid = this.agreement;
            this.agreement_promocode_valid = !this.status || this.agreement_promocode;
            if (!this.form.validate() || !this.agreement_valid || !this.agreement_promocode_valid || this.count < 1) {
                return;
            }
            this.is_ordering = true;
            // override form saving to send headers
            axios.post(this.form.save_url, {
                data: {
                    ...this.showcase3Store.ticketsData,
                    ...this.showcase3Store.contactInfo,
                    promocode: this.showcase3Store.promocode
                },
                trip: this.showcase3Store.trip.id,
                ref: this.form.options['ref'],
                backwardTripId: this.showcase3Store.backwardTrip,
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
        orderWithScheme() {
            this.agreement_valid = this.agreement;
            this.agreement_promocode_valid = !this.status || this.agreement_promocode;
            if (!this.agreement_valid || !this.agreement_promocode_valid || this.showcase3Store.tickets.length < 1) {
                return;
            }
            this.is_ordering = true;
            // override form saving to send headers
            axios.post(this.crm_url + '/showcase/order', {
                data: {
                    ...this.showcase3Store.ticketsData,
                    ...this.showcase3Store.contactInfo,
                    promocode: this.showcase3Store.promocode
                },
                tickets: this.showcase3Store.tickets,
                trip: this.showcase3Store.trip.id,
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
    }
}
</script>

<style lang="scss" scoped>
@import "variables";

.ap-showcase__search {
    box-sizing: border-box;
    padding: 17px 10px;
    border: 1px solid $showcase_light_gray_color;
    display: flex;
    flex-wrap: wrap;
    margin-bottom: 40px;
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

.ap-showcase__select {
    display: flex;
    align-items: center;
    column-gap: 33px;
    margin-bottom: 40px;

    &-title {
        font-family: $showcase_font;
        font-size: 20px;
        font-weight: 700;
        color: #241B5B;
        flex-shrink: 0;
        display: inline-block;
        width: 230px;
    }
}

.ap-input-dropdown {
    height: 60px;
    padding-right: 15px;
}

.ap-showcase__time-wrapper {
    gap: 50px;
    margin-bottom: 50px;
}

.ap-showcase__time {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    width: 386px;
    height: max-content;
}

.ap-showcase__time > p {
    width: 100%;
    font-family: Gilroy;
    font-size: 20px;
    font-weight: 700;
    color: #241B5B;
    height: max-content;
}

.ap-showcase__time > div {
    width: 84px;
    height: 54px;
}

.ap-showcase__time_edin {
    width: 100%;
    height: 54px;
    display: inline-block;
    background-color: #241B5B;
    border-color: #241B5B;
    color: #ffffff;
    line-height: 54px;
    text-align: center;
    box-sizing: border-box;
    padding: 0 20px 0;
    font-family: Gilroy;
    font-size: 14px;
    white-space: nowrap;
    letter-spacing: 0.03rem;
    transition: background-color cubic-bezier(0.24, 0.19, 0.28, 1.29) 150ms, border-color cubic-bezier(0.24, 0.19, 0.28, 1.29) 150ms, color cubic-bezier(0.24, 0.19, 0.28, 1.29) 150ms;

}

.ap-showcase__contacts-wrapper {
    display: flex;
    flex-wrap: wrap;
    margin-bottom: 80px;
}

.ap-showcase__contacts-title {
    color: #241B5B;
    font-family: Gilroy;
    font-size: 24px;
    font-weight: 700;
    margin-bottom: 10px;
    width: 100%;
}

.ap-showcase__contacts-text {
    width: 100%;
    color: #0E0E0E;
    font-family: Gilroy;
    font-size: 18px;
    font-weight: 400;
    margin-bottom: 30px;
}

.ap-showcase__contacts-inner {
    display: flex;
    flex-wrap: wrap;
    gap: 30px;
    align-items: center;
}

.ap-showcase__total-pay {
    width: 276px;
}

.ap-showcase__search_bottom {
    display: none;
}

@media screen and (max-width: 769px) {
    .ap-input-dropdown__list-shown {
        max-width: 100%;
    }
    .ap-showcase__total-pay {
        width: 100%;
    }

    .ap-showcase__contacts-inner {
        gap: 20px;
    }

    .ap-showcase__search {
        margin-bottom: 10px;
    }

    .ap-showcase__select {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
        margin-bottom: 15px;
    }

    .ap-showcase__select-title {
        font-family: Segoe UI;
        font-size: 20px;
        font-weight: 600;
    }

    .ap-showcase__title {
        font-family: Segoe UI;
        font-size: 20px;
        font-weight: 600;
    }

    .ap-showcase__search-item {
        width: 33.33%;
    }

    .ap-radio-wrapper {
        width: 33.33%;
        margin-top: 0;
    }

    .ap-showcase__search-item-2 {
        display: none;
    }

    .ap-showcase__search-item-3 {
        display: none;
    }

    .ap-showcase__search_bottom {
        display: block;
        font-family: Gilroy;
        font-size: 18px;
        font-weight: 500;
        color: #241B5B;
        margin-bottom: 25px;

        span {
            color: #E83B4E;
            font-family: Gilroy;
            font-size: 20px;
            font-weight: 500;
        }
    }

    .ap-showcase__time-wrapper {
        flex-direction: column;
        gap: 25px;
        margin-bottom: 25px;
    }

    .ap-showcase__time {
        width: 100%;
        gap: 14px;
    }

    .ap-showcase__time > p {
        margin-bottom: 0;
    }

    .ap-showcase__time > div {
        width: 77px;
    }

    .ap-showcase__tickets {
        width: 85%;
    }

    .ap-showcase__tickets-table-col-total {
        margin-top: 0;
    }

    .ap-showcase__info {
        display: none;
    }

    .ap-showcase__total-promocode {
        width: 100%;
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
