<template>
    <template v-if="data['tickets'] && data['tickets'].length > 0">
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
            <tr v-for="ticket in data['tickets']">
                <td>{{ ticket['trip_start_date'] }}</td>
                <td>{{ ticket['trip_start_time'] }}</td>
                <td>
                    <div>{{ ticket['excursion'] }}</div>
                    <div style="color: red;" v-if="ticket['backward_price']">обратный</div>
                </td>
                <td>{{ ticket['pier'] }}</td>
                <td>{{ ticket['grade'] }}<br><span
                    v-if="ticket['seat_number']">Место: {{ ticket['seat_number'] }}</span></td>
                <td>{{ ticket['backward_price'] ?? ticket['min_price'] }} -
                    {{ ticket['backward_price'] ?? ticket['max_price'] }}
                </td>
                <template v-if="ticket['available']">
                    <td>
                        <FormNumber v-if="ticket['backward_price'] === null" :form="form"
                                    :name="'tickets.' + ticket['id'] + '.price'" :hide-title="true"/>
                        <FormNumber v-if="ticket['backward_price'] !== null" :model-value="ticket['backward_price']"
                                    :disabled="true" :name="'tickets.' + ticket['id'] + '.price'" :form="form"
                                    :hide-title="true"/>
                    </td>
                    <td>
                        <FormNumber :disabled="ifBackwardTicketOrHaveSeatScheme(ticket)"
                                    :form="form"
                                    :name="'tickets.' + ticket['id'] + '.quantity'" :quantity="true" :min="0"
                                    :hide-title="true" :model-value="ticket['quantity']"
                                    @change="(value) => quantityChange(ticket['id'], value)"
                        />
                    </td>
                    <td class="bold no-wrap">
                        {{
                            multiply(ticket['backward_price'] ?? form.values['tickets.' + ticket['id'] + '.price'], form.values['tickets.' + ticket['id'] + '.quantity'])
                        }}
                    </td>
                </template>
                <template v-else>
                    <td colspan="3" class="text-red text-sm mt-5">Продажа билетов на этот рейс не осуществляется</td>
                </template>
                <td class="va-middle">
                    <GuiActionsMenu
                        v-if="ticket['reverse_excursion_id'] !== null && ticket['backward_price'] === null && !ticket['ticket_provider_id']"
                        :title="null">
                        <span class="link" @click="remove(ticket['id'])">Удалить билет</span>
                        <span v-if="!ticket['backward_price']" class="link"
                              @click="callGetBackwardTrips(ticket['trip_id'], ticket['id'])">Оформить обратный билет</span>
                    </GuiActionsMenu>
                    <GuiIconButton v-if="(ticket['reverse_excursion_id'] === null && ticket['backward_price'] === null)
                     || (ticket['backward_price'] === null && ticket['ticket_provider_id'])
                     || (ticket['reverse_excursion_id'] === null && !ticket['ticket_provider_id'] && ticket['backward_price'] !== null)"
                                   :title="'Удалить из заказа'" :border="false" :color="'red'"
                                   @click="remove(ticket['id'])">
                        <IconCross/>
                    </GuiIconButton>
                </td>
            </tr>
        </table>

        <GuiContainer mt-20 mb-10 bold right>
            <GuiHeading mb-5>Итого к оплате: {{ total }}</GuiHeading>
            <GuiButton
                v-if="data.tickets.filter(ticket => ticket.ticket_provider_id !== null && ticket.reverse_excursion_id !== null).length > 0"
                @click="callGetBackwardTrips(data.ticketTrip, null, data.tickets)">Оформить
                обратный рейс
            </GuiButton>
        </GuiContainer>

        <BackwardTicketSelect mt-20 ref="backwardTicketSelect" @update="backwardTicketsUpdate" :tickets="data.tickets"/>

        <GuiContainer mt-20 mb-20 w-50 pr-30 inline>
            <GuiHeading mb-30 bold>Информация о плательщике</GuiHeading>
            <FormString :form="form" :name="'name'"/>
            <FormString :form="form" :name="'email'"/>
            <FormPhone :form="form" :name="'phone'" required/>
        </GuiContainer>

        <GuiContainer mt-20 w-50 inline>
            <GuiHeading mb-30 bold>Промоутер</GuiHeading>
            <FormString :form="form" :name="'partner_id'" :disabled="form.values['without_partner']"/>
            <FormCheckBox :form="form" :name="'without_partner'" @change="withoutPartnerChanged"/>
        </GuiContainer>

        <GuiContainer w-30 mt-30 inline>
            <GuiButton @click="back">Вернуться к подбору билетов</GuiButton>
        </GuiContainer>

        <GuiContainer w-70 mt-30 inline text-right>
            <GuiButton @click="clear" :color="'red'" :disabled="!canOrder">Очистить</GuiButton>
            <GuiButton @click="order" :color="'green'" :disabled="!canOrder">Оплатить через терминал</GuiButton>
        </GuiContainer>
    </template>
    <template v-else>
        <GuiMessage border>В заказе нет билетов</GuiMessage>
        <GuiContainer mt-30>
            <GuiButton @click="back">Вернуться к подбору билетов</GuiButton>
        </GuiContainer>
    </template>
</template>

<script>
import GuiHeading from "@/Components/GUI/GuiHeading";
import FormNumber from "@/Components/Form/FormNumber";
import GuiIconButton from "@/Components/GUI/GuiIconButton";
import IconCross from "@/Components/Icons/IconCross";
import GuiContainer from "@/Components/GUI/GuiContainer";
import GuiExpand from "@/Components/GUI/GuiExpand";
import FormString from "@/Components/Form/FormString";
import FormPhone from "@/Components/Form/FormPhone";
import FormCheckBox from "@/Components/Form/FormCheckBox";
import GuiButton from "@/Components/GUI/GuiButton";
import GuiMessage from "@/Components/GUI/GuiMessage";
import form from "@/Core/Form";
import deleteEntry from "@/Mixins/DeleteEntry";
import GuiActionsMenu from "../../../Components/GUI/GuiActionsMenu.vue";
import BackwardTicketSelect from "../../../Components/BackwardTicketSelect.vue";

export default {
    props: {
        data: {type: Object, required: true},
    },

    emits: ['update'],

    components: {
        BackwardTicketSelect,
        GuiActionsMenu,
        GuiMessage,
        GuiButton,
        FormCheckBox,
        FormPhone,
        FormString,
        GuiExpand,
        GuiContainer,
        IconCross,
        GuiIconButton,
        FormNumber,
        GuiHeading,
    },

    mixins: [deleteEntry],

    data: () => ({
        form: form(null, '/api/order/terminal/make'),
        show_buyer_info: false,
    }),

    computed: {
        total() {
            if (!this.data['tickets'] || this.data['tickets'].length === 0) {
                return '—';
            }
            let total = 0;
            this.data['tickets'].map(ticket => {
                if (ticket['available'] && !isNaN(ticket['base_price'])) {
                    total += this.multiply(ticket['backward_price'] ?? this.form.values['tickets.' + ticket['id'] + '.price'], this.form.values['tickets.' + ticket['id'] + '.quantity']);
                }
            });
            return this.multiply(total, 1) + ' руб.';
        },

        canOrder() {
            let hasUnavailable = this.data['tickets'].some(ticket => !ticket['available']);
            let count = 0;
            this.data['tickets'].map(ticket => {
                count += ticket['quantity'];
            });
            return !hasUnavailable && count > 0;
        }
    },

    created() {
        this.form.toaster = this.$toast;
    },

    mounted() {
        this.update()
    },

    methods: {
        ifBackwardTicketOrHaveSeatScheme(ticket) {
            return (ticket['backward_price'] !== null || ticket['seat_id'] !== null);
        },
        update() {
            this.form.reset();
            this.data['tickets'].map(ticket => {
                if (ticket['ticket_provider_id'] !== 10) {
                    this.form.set('tickets.' + ticket['id'] + '.price', ticket['base_price'], `numeric|min:${ticket['min_price']}|max:${ticket['max_price']}`, 'Цена', true);
                    this.form.set('tickets.' + ticket['id'] + '.quantity', ticket['quantity'], 'integer|min:0', 'Количество', true);
                } else {
                    this.form.set('tickets.' + ticket['id'] + '.price', ticket['base_price'], `numeric|`, 'Цена', true);
                    this.form.set('tickets.' + ticket['id'] + '.quantity', ticket['quantity'], 'integer|min:0', 'Количество', true);
                }
            });
            this.form.set('partner_id', null, 'required_if:without_partner,false', 'ID промоутера', true);
            this.form.set('without_partner', false, null, 'Без промоутера', true);
            this.form.set('name', null, null, 'Имя', true);
            this.form.set('email', null, 'email|nullable', 'Email', true);
            this.form.set('phone', null, 'required', 'Телефон', true);

            this.form.load();
        },

        back() {
            this.$router.push({name: 'home'});
        },

        callGetBackwardTrips($tripId, $ticketId, tickets) {
            this.$refs.backwardTicketSelect.getBackwardTrips($tripId, $ticketId, tickets);
        },

        backwardTicketsUpdate() {
            this.$emit('update');
            this.$refs.backwardTicketSelect.getBackwardTrips(0, 0);
            location.reload();
        },

        remove(ticket_id) {
            this.deleteEntry('Удалить билеты из заказа?', '/api/cart/terminal/remove', {ticket_id: ticket_id})
                .then(() => {
                    this.data['tickets'] = this.data['tickets'].filter(ticket => ticket['id'] !== ticket_id);
                    this.form.unset('tickets.' + ticket_id + '.quantity');
                    this.form.unset('tickets.' + ticket_id + '.price');
                    this.$emit('update');
                });
        },

        quantityChange(id, value) {
            if (isNaN(value) || value === null) {
                return;
            }
            axios.post('/api/cart/terminal/quantity', {id: id, value: value})
                .then(() => {
                    this.$emit('update')
                })
                .catch(error => {
                    this.$toast.error(error.response.data['message']);
                })
        },

        order() {
            if (!this.canOrder || !this.form.validate()) {
                return;
            }
            this.$dialog.show('Завершить оформление заказа и отправить в оплату через терминал?', 'question', 'orange', [
                this.$dialog.button('ok', 'Продолжить', 'orange'),
                this.$dialog.button('cancel', 'Отмена'),
            ], 'center')
                .then((result) => {
                    if (result === 'ok') {
                        this.form.options['mode'] = 'order';
                        this.form.save()
                            .then(() => {
                                this.$store.dispatch('terminal/refresh');
                                this.$emit('update');
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
                        axios.post('/api/cart/terminal/clear', {})
                            .then(() => {
                                this.$store.dispatch('terminal/refresh');
                                this.$emit('update');
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

        withoutPartnerChanged(value) {
            if (value) {
                this.form.validate('partner_id');
            }
        }
    }
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
        padding: 0 10px;
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
</style>
