<template>
    <page :title="$route.meta['title']" :loading="processing">
        <template v-if="data.data && data.data['tickets'] && data.data['tickets'].length > 0">
            <GuiHeading mt-20 bold>Состав заказа</GuiHeading>
            <table class="rates-table rates-table__border mt-20 w-100">
                <thead>
                <tr>
                    <th class="p-10">Дата</th>
                    <th class="p-10">Время</th>
                    <th class="p-10">Экскурсия</th>
                    <th class="p-10">Причал</th>
                    <th class="p-10">Тип билета</th>
                    <th class="p-10">Цена</th>
                    <th class="p-10">Количество</th>
                    <th class="p-10">Стоимость</th>
                    <th></th>
                </tr>
                </thead>
                <tr v-for="ticket in data.data['tickets']">
                    <td class="p-10 va-middle">{{ ticket['trip_start_date'] }}</td>
                    <td class="p-10 va-middle">{{ ticket['trip_start_time'] }}</td>
                    <td class="p-10 va-middle">
                        <div>{{ ticket['excursion'] }}</div>
                    </td>
                    <td class="p-10 va-middle">{{ ticket['pier'] }}</td>
                    <td class="p-10 va-middle">{{ ticket['grade'] }}</td>
                    <td class="p-10 bold text-md no-wrap va-middle">{{ ticket['available'] ? ticket['base_price'] + ' руб.' : '—' }}</td>
                    <td class="pt-5 va-middle">
                        <data-field-input v-if="ticket['available']" :datasource="form" :name="'tickets.' + ticket['id'] + '.quantity'" :type="'number'"/>
                        <div v-else class="text-red text-sm mt-5">Продажа билетов на этот рейс не осуществляется</div>
                    </td>
                    <td class="p-10 bold text-md no-wrap va-middle">
                        {{ ticket['available'] ? (ticket['base_price'] * form.values['tickets.' + ticket['id'] + '.quantity']) + ' руб.' : '—' }}
                    </td>
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
                <data-field-input :datasource="form" :name="'name'"/>
                <data-field-input :datasource="form" :name="'email'"/>
                <data-field-masked-input :datasource="form" :name="'phone'" :mask="'+7 (###) ###-##-##'"/>
            </GuiContainer>
            <GuiContainer w-30 mt-30 inline>
                <GuiButton @click="back">Вернуться к подбору билетов</GuiButton>
            </GuiContainer>
            <GuiContainer w-70 mt-30 inline text-right>
                <GuiButton @click="reserve" :color="'green'" :disabled="hasUnavailable" v-if="data.data['can_reserve']">Оформить бронь</GuiButton>
                <GuiButton @click="order" :color="'green'" :disabled="hasUnavailable">Оплатить с лицевого счёта</GuiButton>
            </GuiContainer>
        </template>
        <template v-else>
            <GuiMessage>В заказе нет билетов</GuiMessage>
            <GuiContainer mt-30>
                <GuiButton @click="back">Вернуться к подбору билетов</GuiButton>
            </GuiContainer>
        </template>
    </page>
</template>

<script>
import Page from "@/Layouts/Page";
import data from "@/Core/Data";
import GuiMessage from "@/Components/GUI/GuiMessage";
import GuiHeading from "@/Components/GUI/GuiHeading";
import GuiHint from "@/Components/GUI/GuiHint";
import GuiContainer from "@/Components/GUI/GuiContainer";
import GuiButton from "@/Components/GUI/GuiButton";
import formDataSource from "@/Helpers/Core/formDataSource";
import DataFieldInput from "@/Components/DataFields/DataFieldInput";
import DataFieldMaskedInput from "@/Components/DataFields/DataFieldMaskedInput";
import GuiIconButton from "@/Components/GUI/GuiIconButton";
import IconCross from "@/Components/Icons/IconCross";
import deleteEntry from "@/Mixins/DeleteEntry";

export default {
    components: {
        IconCross,
        GuiIconButton,
        DataFieldMaskedInput,
        DataFieldInput,
        GuiButton,
        GuiContainer,
        GuiHint,
        GuiHeading,
        GuiMessage,
        Page,
    },

    mixins: [deleteEntry],

    data: () => ({
        data: data('/api/order/get'),
        form: formDataSource(null, '/api/order/make'),
    }),

    computed: {
        processing() {
            return this.data.is_loading || this.form.saving;
        },

        total() {
            if (!this.data.loaded || !this.data.data['tickets'] || this.data.data['tickets'].length === 0) {
                return '—';
            }
            let total = 0;
            this.data.data['tickets'].map(ticket => {
                if (ticket['available'] && !isNaN(ticket['base_price'])) {
                    total += ticket['base_price'] * this.form.values['tickets.' + ticket['id'] + '.quantity'];
                }
            });
            return total + ' руб.';
        },

        hasUnavailable() {
            return this.data.data['tickets'].some(ticket => !ticket['available']);
        }
    },

    created() {
        this.load();
        this.form.toaster = this.$toast;
    },

    methods: {
        load() {
            this.data.load()
                .then((data) => {
                    this.form.reset();
                    data['tickets'].map(ticket => {
                        this.form.setField('tickets.' + ticket['id'] + '.quantity', ticket['quantity'], 'integer|min:0', 'Количество', true);
                    });
                    this.form.setField('name', null, null, 'Имя', true);
                    this.form.setField('email', null, 'email|nullable', 'Email', true);
                    this.form.setField('phone', null, null, 'Телефон', true);

                    this.form.loaded = true;
                });
        },

        back() {
            this.$router.push({name: 'tickets-select'});
        },

        remove(ticket_id) {
            this.deleteEntry('Удалить билеты из заказа?', '/api/order/remove', {ticket_id: ticket_id})
                .then(() => {
                    this.data.data['tickets'] = this.data.data['tickets'].filter(ticket => ticket['id'] !== ticket_id);
                    this.form.unset('tickets.' + ticket_id + '.quantity');
                    this.$store.dispatch('partner/refresh');
                });
        },

        reserve() {
            if (this.hasUnavailable) {
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
                                this.$store.dispatch('partner/refresh');
                                this.load();
                            });
                    }
                });
        },

        order() {
            if (this.hasUnavailable) {
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
                                this.$store.dispatch('partner/refresh');
                                this.load();
                            });
                    }
                });
        },
    },
}
</script>
