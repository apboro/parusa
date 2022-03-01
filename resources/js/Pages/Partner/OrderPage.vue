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
                    <th class="w-110px">Цена</th>
                    <th>Количество</th>
                    <th class="w-110px">Стоимость</th>
                    <th></th>
                </tr>
                </thead>
                <tr v-for="ticket in data.data['tickets']">
                    <td>{{ ticket['trip_start_date'] }}</td>
                    <td>{{ ticket['trip_start_time'] }}</td>
                    <td>
                        <div>{{ ticket['excursion'] }}</div>
                    </td>
                    <td>{{ ticket['pier'] }}</td>
                    <td>{{ ticket['grade'] }}</td>
                    <template v-if="ticket['available']">
                        <td class="bold no-wrap">{{ ticket['base_price'] }} руб.</td>
                        <td>
                            <FormNumber :form="form" :name="'tickets.' + ticket['id'] + '.quantity'" :quantity="true" :min="0" :hide-title="true"/>
                        </td>
                        <td class="bold no-wrap">
                            {{ multiply(ticket['base_price'], form.values['tickets.' + ticket['id'] + '.quantity']) }} руб.
                        </td>
                    </template>
                    <template v-else>
                        <td colspan="3" class="text-red text-sm mt-5">Продажа билетов на этот рейс не осуществляется</td>
                    </template>
                    <td class="va-middle">
                        <GuiIconButton :title="'Удалить из заказа'" :border="false" :color="'red'" @click="remove(ticket['id'])">
                            <IconCross/>
                        </GuiIconButton>
                    </td>
                </tr>
            </table>
            <GuiHeading mt-20 bold right>Итого к оплате: {{ total }}</GuiHeading>
            <GuiContainer mt-20 mb-20 w-50>
                <GuiHeading mb-30 bold>Информация о плательщике</GuiHeading>
                <GuiHint mb-30>Информация предоставляется на случай, если поупателя нужно будет уведомить об отмене рейса или иных непредвиденных обстаятельствах. Не является
                    обязательной.
                </GuiHint>
                <FormString :form="form" :name="'name'"/>
                <FormString :form="form" :name="'email'"/>
                <FormPhone :form="form" :name="'phone'"/>
            </GuiContainer>

            <GuiContainer w-30 mt-30 inline>
                <GuiButton @click="back">Вернуться к подбору билетов</GuiButton>
            </GuiContainer>

            <GuiContainer w-70 mt-30 inline text-right>
                <GuiButton @click="reserve" :color="'green'" :disabled="!canOrder" v-if="data.data['can_reserve']">Оформить бронь</GuiButton>
                <GuiButton @click="order" :color="'green'" :disabled="!canOrder">Оплатить с лицевого счёта</GuiButton>
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

export default {
    components: {
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
        data: data('/api/cart'),
        form: form(null, '/api/order/make'),
    }),

    computed: {
        processing() {
            return this.data.is_loading || this.form.saving;
        },

        total() {
            if (!this.data.is_loaded || !this.data.data['tickets'] || this.data.data['tickets'].length === 0) {
                return '—';
            }
            let total = 0;
            this.data.data['tickets'].map(ticket => {
                if (ticket['available'] && !isNaN(ticket['base_price'])) {
                    total += this.multiply(ticket['base_price'], this.form.values['tickets.' + ticket['id'] + '.quantity']);
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
        }
    },

    created() {
        this.load();
        this.form.toaster = this.$toast;
    },

    methods: {
        load() {
            this.data.load()
                .then(data => {
                    this.form.reset();
                    data.data['tickets'].map(ticket => {
                        this.form.set('tickets.' + ticket['id'] + '.quantity', ticket['quantity'], 'integer|min:0', 'Количество', true);
                    });
                    this.form.set('name', null, null, 'Имя', true);
                    this.form.set('email', null, 'email|nullable', 'Email', true);
                    this.form.set('phone', null, null, 'Телефон', true);

                    this.form.load();
                });
        },

        back() {
            this.$router.push({name: 'tickets-select'});
        },

        remove(ticket_id) {
            this.deleteEntry('Удалить билеты из заказа?', '/api/cart/remove', {ticket_id: ticket_id})
                .then(() => {
                    this.data.data['tickets'] = this.data.data['tickets'].filter(ticket => ticket['id'] !== ticket_id);
                    this.form.unset('tickets.' + ticket_id + '.quantity');
                    this.$store.dispatch('partner/refresh');
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
                            .then((values, payload) => {
                                console.log(values, payload);
                                this.$store.dispatch('partner/refresh');
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
                            .then((values, payload) => {
                                console.log(values, payload);
                                this.$store.dispatch('partner/refresh');
                                this.$router.push({name: 'order-info', params: {id: this.form.payload['order_id']}});
                            });
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

    &:deep .input-number__input {
        text-align: center;
    }

    & .input-field__errors-error {
        font-size: 12px;
    }

}
</style>
