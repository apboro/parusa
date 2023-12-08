<template>
    <LayoutPage :title="$route.meta['title']" :loading="processing">
        <template v-if="data.data && data.data['tickets'] && data.data['tickets'].length > 0">
            <GuiHeading mt-20 bold>Состав заказа</GuiHeading>
            <table class="order-table">
                <thead>
                <tr>
                    <th>Дата</th>
                    <th>Время</th>
                    <th>Экскурсия</th>
                    <th>Причал</th>
                    <th>Тип билета</th>
                    <th class="w-90px">Диапазон цен, руб</th>
                    <th class="w-90px">Цена, руб</th>
                    <th>Количество</th>
                    <th class="w-110px">Стоимость</th>
                    <th></th>
                </tr>
                </thead>
                <tr v-for="ticket in tickets"
                    :class="{'order-table__first' : ticket['first_in_date'], 'order-table__highlight' : ticket['trip_flag']}">
                    <td><span class="order-table__mobile-subtitle">Дата</span>{{ ticket['trip_start_date'] }}</td>
                    <td><span class="order-table__mobile-subtitle">Время</span>{{ ticket['trip_start_time'] }}</td>
                    <td>
                        <span class="order-table__mobile-subtitle">Экскурсия</span>
                        <div>{{ ticket['excursion'] }}
                            <div style="color: red;" v-if="ticket['backward_price']">обратный</div>
                        </div>
                    </td>
                    <td><span class="order-table__mobile-subtitle">Причал</span>{{ ticket['pier'] }}</td>
                    <td><span class="order-table__mobile-subtitle">Тип билета</span>{{ ticket['grade'] }}<br><span v-if="ticket['seat']">Место: {{ticket['seat']['seat_number']}}</span></td>
                    <td><span class="order-table__mobile-subtitle">Диапазон цен, руб</span>{{ ticket['backward_price'] ?? ticket['min_price'] }} -
                        {{ ticket['backward_price'] ?? ticket['max_price'] }}
                    </td>
                    <template v-if="ticket['available']">
                        <td>
                            <span class="order-table__mobile-subtitle">Цена, руб</span>
                            <FormNumber v-if="ticket['backward_price'] === null" :form="form"
                                        :name="'tickets.' + ticket['id'] + '.price'" :hide-title="true"/>
                            <FormNumber v-if="ticket['backward_price'] !== null" :model-value="ticket['backward_price']"
                                        :disabled="true" :name="'tickets.' + ticket['id'] + '.price'" :form="form"
                                        :hide-title="true"/>
                        </td>
                        <td>
                            <span class="order-table__mobile-subtitle">Количество</span>
                            <FormNumber :disabled="(ticket['backward_price'] !== null && ticket['ticket_provider_id'] !== null  || ticket['seat'] !== null)" :form="form"
                                        :name="'tickets.' + ticket['id'] + '.quantity'" :quantity="true"
                                        :min="0" :hide-title="true" :model-value="ticket['quantity']"
                                        @change="(value) => quantityChange(ticket['id'], value)"
                            />
                        </td>
                        <td class="bold no-wrap">
                            <span class="order-table__mobile-subtitle">Стоимость</span>
                            {{
                                multiply(ticket['backward_price'] ?? ticket['base_price'], form.values['tickets.' + ticket['id'] + '.quantity'])
                            }}
                            руб.
                        </td>
                    </template>
                    <template v-else>
                        <td colspan="3" class="text-red text-sm mt-5">Продажа билетов на этот рейс не осуществляется
                        </td>
                    </template>
                    <td class="va-middle">
                        <GuiActionsMenu
                            v-if="ticket['reverse_excursion_id'] !== null && ticket['backward_price'] === null && !ticket['ticket_provider_id']"
                            :title="null">
                            <span class="link" @click="remove(ticket['id'])">Удалить билет</span>
                            <span v-if="!ticket['backward_price']" class="link"
                                  @click="callGetBackwardTrips(ticket['trip_id'], ticket['id'], null)">Оформить обратный билет</span>
                        </GuiActionsMenu>
                        <GuiIconButton
                            v-if="ticket['reverse_excursion_id'] === null || ticket['backward_price'] !== null || ticket['ticket_provider_id'] !== null"
                            :title="'Удалить из заказа'" :border="false" :color="'red'"
                            @click="remove(ticket['id'])">
                            <IconCross/>
                        </GuiIconButton>
                    </td>
                </tr>
            </table>


            <GuiContainer mt-20 mb-10 bold right order-table__mobile-total>
                <GuiHeading mb-5 >Итого к оплате: {{ total }}</GuiHeading>
                <GuiButton v-if="tickets.filter(ticket => ticket.ticket_provider_id !== null && ticket.reverse_excursion_id !== null).length > 0"
                           @click="callGetBackwardTrips(data.data.ticketTrip, null, tickets)">Оформить
                    обратный рейс
                </GuiButton>
            </GuiContainer>

            <BackwardTicketSelect mt-20 ref="backwardTicketSelect" :tickets="data.data['tickets']"
                                  @update="backwardTicketsUpdate"/>


            <GuiContainer mt-20 mb-20 w-50 mobile-partner__info>
                <GuiHeading mb-30 bold>Информация о плательщике</GuiHeading>
                <GuiHint mb-30>Информация предоставляется на случай, если покупателя нужно будет уведомить об отмене
                    рейса или иных непредвиденных обстоятельствах. Не является
                    обязательной.
                </GuiHint>
                <FormString :form="form" :name="'name'"/>
                <FormString :form="form" :name="'email'" required/>
                <FormPhone :form="form" :name="'phone'" required/>
            </GuiContainer>

            <GuiContainer w-30 mt-30 inline mobile-partner__button-top>
                <GuiButton @click="back">Вернуться к подбору билетов</GuiButton>
            </GuiContainer>

            <GuiContainer w-70 mt-30 inline text-right mobile-partner__button-bottom>
                <GuiButton @click="clear" :color="'red'" :disabled="!canOrder">Очистить</GuiButton>
                <GuiButton @clicked="order" :color="'green'" :disabled="!canOrder || !data.data['openshift']">{{data.data['openshift'] ? 'Оформить' : 'Не открыта смена'}}</GuiButton>
            </GuiContainer>
        </template>
        <template v-else>
            <GuiMessage>В заказе нет билетов</GuiMessage>
            <GuiContainer mt-30>
                <GuiButton @click="back">Вернуться к подбору билетов</GuiButton>
            </GuiContainer>
        </template>
    </LayoutPage>
</template>

<script>
import data from "@/Core/Data";
import form from "@/Core/Form";
import LayoutPage from "@/Components/Layout/LayoutPage";
import GuiMessage from "@/Components/GUI/GuiMessage";
import GuiHeading from "@/Components/GUI/GuiHeading";
import GuiHint from "@/Components/GUI/GuiHint";
import GuiContainer from "@/Components/GUI/GuiContainer";
import GuiButton from "@/Components/GUI/GuiButton";
import GuiIconButton from "@/Components/GUI/GuiIconButton";
import IconCross from "@/Components/Icons/IconCross";
import deleteEntry from "@/Mixins/DeleteEntry";
import FormString from "@/Components/Form/FormString";
import FormPhone from "@/Components/Form/FormPhone";
import FormNumber from "@/Components/Form/FormNumber";
import BackwardTicketSelect from "@/Components/BackwardTicketSelect";
import GuiActionsMenu from "../../Components/GUI/GuiActionsMenu.vue";
import GuiText from "@/Components/GUI/GuiText.vue";

export default {
    components: {
        GuiText,
        GuiActionsMenu,
        BackwardTicketSelect,
        FormNumber,
        FormPhone,
        FormString,
        LayoutPage,
        IconCross,
        GuiIconButton,
        GuiButton,
        GuiContainer,
        GuiHint,
        GuiHeading,
        GuiMessage,
    },

    mixins: [deleteEntry],

    data: () => ({
        data: data('/api/cart/promoter'),
        form: form(null, '/api/order/promoter/make'),
    }),

    computed: {
        processing() {
            return this.data.is_loading || this.form.is_saving;
        },
        total() {
            if (!this.data.is_loaded || !this.data.data['tickets'] || this.data.data['tickets'].length === 0) {
                return '—';
            }
            let total = 0;
            this.data.data['tickets'].map(ticket => {
                if (ticket['available'] && !isNaN(ticket['base_price'])) {
                    total += this.multiply(ticket['backward_price'] ?? ticket['base_price'], this.form.values['tickets.' + ticket['id'] + '.quantity']);
                }
            });
            return this.multiply(total, 1) + ' руб.';
        },
        canOrder() {
            let hasUnavailable = this.data.data['tickets'].some(ticket => !ticket['available']);
            let count = 0;
            this.data.data['tickets'].map(ticket => {
                count += this.form.values['tickets.' + ticket['id'] + '.quantity'];
            });
            return !hasUnavailable && count > 0;
        },
        tickets() {
            if (!this.data.data || !this.data.data['tickets'] || !this.data.data['tickets'].length > 0) {
                return [];
            }
            let last_trip = null;
            let trip_flag = false;
            let last_date = null;
            let date_flag = false;
            return this.data.data['tickets'].map(ticket => {
                // check trip and date changed
                ticket['first_in_trip'] = last_trip !== null && ticket['trip_id'] !== last_trip;
                ticket['first_in_date'] = last_date !== null && ticket['trip_start_date'] !== last_date;

                // add trip and date grouping
                if (last_trip === null) last_trip = ticket['trip_id'];
                if (last_date === null) last_date = ticket['trip_start_date'];
                if (last_trip !== ticket['trip_id']) trip_flag = !trip_flag;
                if (last_date !== ticket['trip_start_date']) date_flag = !date_flag;
                ticket['trip_flag'] = trip_flag;
                ticket['date_flag'] = date_flag;

                last_trip = ticket['trip_id'];
                last_date = ticket['trip_start_date'];

                return ticket;
            });
        },
    },

    created() {
        this.load();
        this.form.toaster = this.$toast;
    },

    methods: {
        load(name = null, email = null, phone = null) {
            this.data.load()
                .then(data => {
                    this.form.reset();
                    data.data['tickets'].map(ticket => {
                        this.form.set('tickets.' + ticket['id'] + '.price', ticket['base_price'], `numeric|min:${ticket['min_price']}|max:${ticket['max_price']}`, 'Цена', true);
                        this.form.set('tickets.' + ticket['id'] + '.quantity', ticket['quantity'], 'integer|min:0', 'Количество', true);
                    });
                    this.form.set('name', name, null, 'Имя', true);
                    this.form.set('email', email, 'email|nullable', 'Email', true);
                    this.form.set('phone', phone, null, 'Телефон', true);

                    this.form.load();
                });
        },


        callGetBackwardTrips($tripId, $ticketId, tickets) {
            this.$refs.backwardTicketSelect.getBackwardTrips($tripId, $ticketId, tickets);
        },

        back() {
            this.$router.push({name: 'home'});
        },

        quantityChange(id, value) {
            if (isNaN(value) || value === null) {
                return;
            }
            axios.post('/api/cart/promoter/quantity', {id: id, value: value})
                .then(() => {
                    this.load(this.form.values['name'], this.form.values['email'], this.form.values['phone']);
                    this.$store.dispatch('promoter/refresh');
                })
                .catch(error => {
                    this.$toast.error(error.response.data['message']);
                })
        },

        backwardTicketsUpdate() {
            this.load(this.form.values['name'], this.form.values['email'], this.form.values['phone']);
            this.data.data['tickets'] = [];
            this.$emit('update');
            this.$store.dispatch('promoter/refresh');
        },

        remove(ticket_id) {
            this.deleteEntry('Удалить билеты из заказа?', '/api/cart/promoter/remove', {ticket_id: ticket_id})
                .then(() => {
                    this.load(this.form.values['name'], this.form.values['email'], this.form.values['phone']);
                    this.$store.dispatch('promoter/refresh');
                });
        },

        reserve() {
            if (!this.canOrder) {
                return;
            }
            this.$dialog.show('Добавить билеты в бронь?', 'question', 'orange', [
                this.$dialog.button('ok', 'Продолжить', 'orange'),
                this.$dialog.button('cancel', 'Отмена'),
            ], 'center')
                .then((result) => {
                    if (result === 'ok') {
                        this.form.options['mode'] = 'reserve';
                        this.form.save()
                            .then(() => {
                                this.$store.dispatch('promoter/refresh');
                                this.$router.push({name: 'order-info', params: {id: this.form.payload['order_id']}});
                            });
                    }
                });
        },

        order() {
            if (!this.canOrder) {
                return;
            }
            this.$dialog.show('Оформить заказ и оплатить с лицевого счёта?', 'question', 'orange', [
                this.$dialog.button('ok', 'Продолжить', 'orange'),
                this.$dialog.button('cancel', 'Отмена'),
            ], 'center')
                .then((result) => {
                    if (result === 'ok') {
                        this.form.options['mode'] = 'order';
                        this.form.save()
                            .then(() => {
                                this.$store.dispatch('promoter/refresh');
                                this.$router.push({name: 'order-info', params: {id: this.form.payload['order_id']}});
                            });
                    }
                });
        },

        clear() {
            this.$dialog.show('Очистить содержимое заказа?', 'question', 'red', [
                this.$dialog.button('ok', 'Очистить', 'red'),
                this.$dialog.button('cancel', 'Отмена'),
            ], 'center')
                .then((result) => {
                    if (result === 'ok') {
                        axios.post('/api/cart/promoter/clear', {})
                            .then(() => {
                                this.load();
                                this.$store.dispatch('promoter/refresh');
                            })
                            .catch(error => {
                                this.$toast.error(error.response.data['message']);
                            })
                    }
                });
        },

        multiply(a, b) {
            return Math.ceil(a * b * 100) / 100;
        },
    },
}
</script>

<style lang="scss" scoped>
$project_font: -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica Neue, Arial, Noto Sans, sans-serif, Apple Color Emoji, Segoe UI Emoji, Segoe UI Symbol, Noto Color Emoji !default;
$base_black_color: #1e1e1e !default;

.order-table {
    font-size: 14px;
    font-family: $project_font;
    border-collapse: collapse;
    margin: 10px 0 0;
    width: 100%;
    color: $base_black_color;

    &__mobile-subtitle {
        display: none;
    }

    &__first {
        border-top: 1px solid #999999;
    }

    &__highlight {
        background-color: #f1f1f1;
    }

    & thead {
        color: #424242;

        & td {
            vertical-align: middle;
        }
    }

    & th {
        text-align: left;
        padding: 10px;
        cursor: default;
    }

    & td {
        vertical-align: middle;
        padding: 5px 10px;
        cursor: default;
    }

    & .input-field {
        width: 140px;
    }

    &:deep(.input-number__input) {
        text-align: center;
    }

    & .input-field__errors-error {
        font-size: 12px;
    }

}

@media (max-width: 767px) {
    .order-table {
        thead {
            display: none;
        }

        &__mobile-subtitle {
            display: block;
            font-weight: 500;
            margin-bottom: 5px;
        }

        >tr {
            display: flex;
            flex-direction: column;
            row-gap: 10px;
        }

        >tr:not(:first-of-type) {
            border-top: 1px solid #888;
            padding-top: 10px;
        }

        &__mobile-total {
            margin-top: 0;
            margin-bottom: 40px;
        }
    }

    .mobile-partner__info {
        width: 100%;
    }

    .mobile-partner__button-top {
        width: 100%;
        margin-top: 40px;
    }

    .mobile-partner__button-bottom {
        width: 100%;
        margin-top: 0;
    }
}
</style>
