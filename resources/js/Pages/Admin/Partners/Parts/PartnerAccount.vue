<template>
    <LoadingProgress :loading="this.list.is_loading">
        <GuiContainer mt-15 mb-15 border p-20>
            <GuiContainer inline mr-50>
                <GuiHeading text-md mb-10>Баланс лицевого счёта</GuiHeading>
                <GuiHeading>{{ list.payload['balance'] }} руб.</GuiHeading>
            </GuiContainer>
            <GuiContainer inline>
                <GuiHeading text-md mb-10>Лимит по лицевому счёту</GuiHeading>
                <GuiHeading>{{ list.payload['limit'] }} руб.</GuiHeading>
                <span class="link text-sm flex mt-10" v-if="editable" @click="limitChange">Изменить</span>
            </GuiContainer>
        </GuiContainer>

        <LayoutFilters>
            <LayoutFiltersItem :title="'Период'">
                <InputDate
                    v-model="list.filters['date_from']"
                    :original="list.filters_original['date_from']"
                    @change="list.load()"
                />
                <InputDate
                    v-model="list.filters['date_to']"
                    :original="list.filters_original['date_to']"
                    :from="list.filters['date_from']"
                    @change="list.load()"
                />
            </LayoutFiltersItem>
            <LayoutFiltersItem :title="'Тип операции'">
                <DictionaryDropDown
                    v-model="list.filters['transaction_type_id']"
                    :dictionary="'transaction_primary_types'"
                    :original="list.filters_original['transaction_type_id']"
                    :placeholder="'Все'"
                    :has-null="true"
                    @change="list.load()"
                />
            </LayoutFiltersItem>
            <template #search>
                <GuiActionsMenu :class="'self-align-end'" :title="'Операции'" v-if="editable">
                    <span class="link" @click="editRefill(null)">Пополнение счёта</span>
                    <span class="link" @click="editWithdrawal(null)">Списание баланса</span>
                </GuiActionsMenu>
            </template>
        </LayoutFilters>

        <GuiContainer w-100>
            <ListTable v-if="list.list.length > 0" :titles="list.titles" :has-action="editable">
                <ListTableRow v-for="transaction in list.list">
                    <ListTableCell>
                        {{ transaction['timestamp'] }}
                    </ListTableCell>
                    <ListTableCell>
                        {{ transaction['type'] }}
                    </ListTableCell>
                    <ListTableCell :nowrap="true" :class="{'text-dark-green': transaction['sign'] === 1, 'text-dark-red': transaction['sign'] === -1}">
                        {{ transaction['sign'] === -1 ? '-' : '+' }} {{ transaction['amount'] }} руб.
                    </ListTableCell>
                    <ListTableCell>
                        <template v-if="transaction['reason_raw']">
                            {{ transaction['reason_raw']['title'] }}
                            <template v-if="transaction['reason_raw']['object'] === 'order'">
                                <router-link v-if="transaction['reason_raw']['object_id']" class="link"
                                             :to="{name: 'order-info', params: {id: transaction['reason_raw']['object_id'] }}">
                                    {{ transaction['reason_raw']['caption'] }}
                                </router-link>
                                <template v-else>
                                    {{ transaction['reason_raw']['caption'] }}
                                </template>
                            </template>
                            <template v-else-if="transaction['reason_raw']['object'] === 'ticket'">
                                <router-link v-if="transaction['reason_raw']['object_id']" class="link"
                                             :to="{name: 'ticket-info', params: {id: transaction['reason_raw']['object_id'] }}">
                                    {{ transaction['reason_raw']['caption'] }}
                                </router-link>
                                <template v-else>
                                    {{ transaction['reason_raw']['caption'] }}
                                </template>
                            </template>
                        </template>
                        <GuiHint v-if="transaction['comments']" mt-10>{{ transaction['comments'] }}</GuiHint>
                    </ListTableCell>
                    <ListTableCell :nowrap="true">
                        {{ transaction['operator'] }}
                    </ListTableCell>
                    <ListTableCell v-if="editable">
                        <GuiActionsMenu :title="null" v-if="transaction['editable'] || transaction['deletable']">
                            <span class="link" v-if="transaction['editable'] && transaction['type_id'] !== 4" @click="editRefill(transaction)">Редактировать</span>
                            <span class="link" v-if="transaction['editable'] && transaction['type_id'] === 4" @click="editWithdrawal(transaction)">Редактировать</span>
                            <span class="link" v-if="transaction['deletable']" @click="remove(transaction)">Удалить</span>
                        </GuiActionsMenu>
                    </ListTableCell>
                </ListTableRow>
            </ListTable>
            <GuiMessage border v-else-if="list.is_loaded">Нет операций за выбранный период</GuiMessage>
            <Pagination :pagination="list.pagination" @pagination="(page, per_page) => list.load(page, per_page)"/>
        </GuiContainer>


        <GuiContainer mt-30>
            <GuiValue :class="'w-300px'" :title="'Сумма по выборке'">{{ list.payload['selected_total'] }} руб.</GuiValue>
            <GuiValue :class="'w-300px'" :title="'Сумма на странице'">{{ list.payload['selected_page_total'] }} руб.</GuiValue>
        </GuiContainer>

        <GuiContainer mt-30>
            <GuiHeading text-lg>Статистика по счёту за период <span class="bold">{{ list.payload['date_from'] }} - {{ list.payload['date_to'] }}</span></GuiHeading>
            <GuiHeading text-md mt-20>Состояние лицевого счёта:</GuiHeading>
            <GuiContainer inline pr-40 pt-15>
                <GuiValue :class="'w-300px'" :title="'На начало периода'">{{ list.payload['period_start_amount'] }} руб.</GuiValue>
                <GuiValue :class="'w-300px'" :title="'На конец периода'">{{ list.payload['period_end_amount'] }} руб.</GuiValue>
                <GuiValue :class="'w-300px'" :title="'Сальдо'">{{ list.payload['period_income_amount'] }} руб.</GuiValue>
            </GuiContainer>
            <!--
            <GuiContainer inline pr-40 pt-15>
                <GuiValue :class="'w-300px'" :title="'Сумма продаж'">{{ list.payload['period_sell_amount'] }} руб.</GuiValue>
                <GuiValue :class="'w-300px'" :title="'Начислено комиссионных'">{{ list.payload['period_commission_amount'] }} руб.</GuiValue>
                <GuiValue :class="'w-300px'" :title="'Состав продаж, заказы/билеты'">{{ list.payload['period_sell_orders'] }} / {{ list.payload['period_sell_tickets'] }}</GuiValue>
            </GuiContainer>
            -->
        </GuiContainer>

        <FormPopUp ref="popup_limit" v-if="editable"
                   :title="'Лимит по лицевому счёту'"
                   :form="limit_form"
                   :options="{id: this.partnerId}"
        >
            <FormNumber :form="limit_form" :name="'limit'" :hide-title="true"/>
        </FormPopUp>

        <FormPopUp ref="popup_refill" v-if="editable"
                   :title="'Пополнение счёта'"
                   :form="refill_form"
                   :options="{partnerId: this.partnerId, transactionId: transaction}"
        >
            <GuiContainer w-500px>
                <FormDictionary :form="refill_form" :dictionary="'transaction_refill_types'" :name="'type_id'" @change="typeChanged" :disabled="transaction !== 0"/>
                <FormDate :form="refill_form" :name="'timestamp'" v-if="refill_form.values['type_id'] !== null"/>
                <FormString :form="refill_form" :name="'reason'" v-if="has_reason && refill_form.values['type_id'] !== null"/>
                <FormDate :form="refill_form" :name="'reason_date'" v-if="has_reason_date && refill_form.values['type_id'] !== null"/>
                <FormNumber :form="refill_form" :name="'amount'" :type="'number'" v-if="refill_form.values['type_id'] !== null"/>
                <FormText :form="refill_form" :name="'comments'" v-if="refill_form.values['type_id'] !== null"/>
            </GuiContainer>
        </FormPopUp>

        <FormPopUp ref="popup_withdrawal" v-if="editable"
                   :title="'Списание баланса'"
                   :form="withdrawal_form"
                   :options="{partnerId: this.partnerId, transactionId: transaction}"
        >
            <GuiContainer w-500px>
                <FormDictionary :form="withdrawal_form" :dictionary="'transaction_refill_types'" :name="'type_id'" :disabled="true"/>
                <FormDate :form="withdrawal_form" :name="'timestamp'"/>
                <FormString :form="withdrawal_form" :name="'reason'"/>
                <FormDate :form="withdrawal_form" :name="'reason_date'"/>
                <FormNumber :form="withdrawal_form" :name="'amount'" :type="'number'"/>
                <FormText :form="withdrawal_form" :name="'comments'"/>
            </GuiContainer>
        </FormPopUp>
    </LoadingProgress>
</template>

<script>
import deleteEntry from "@/Mixins/DeleteEntry";
import list from "@/Core/List";
import LoadingProgress from "@/Components/LoadingProgress";
import GuiContainer from "@/Components/GUI/GuiContainer";
import GuiHeading from "@/Components/GUI/GuiHeading";
import LayoutFilters from "@/Components/Layout/LayoutFilters";
import LayoutFiltersItem from "@/Components/Layout/LayoutFiltersItem";
import InputDate from "@/Components/Inputs/InputDate";
import DictionaryDropDown from "@/Components/Inputs/DictionaryDropDown";
import GuiActionsMenu from "@/Components/GUI/GuiActionsMenu";
import ListTable from "@/Components/ListTable/ListTable";
import GuiMessage from "@/Components/GUI/GuiMessage";
import Pagination from "@/Components/Pagination";
import ListTableRow from "@/Components/ListTable/ListTableRow";
import ListTableCell from "@/Components/ListTable/ListTableCell";
import GuiHint from "@/Components/GUI/GuiHint";
import GuiValue from "@/Components/GUI/GuiValue";
import form from "@/Core/Form";
import FormPopUp from "@/Components/FormPopUp";
import FormNumber from "@/Components/Form/FormNumber";
import FormDictionary from "@/Components/Form/FormDictionary";
import FormDate from "@/Components/Form/FormDate";
import FormString from "@/Components/Form/FormString";
import FormText from "@/Components/Form/FormText";


export default {
    props: {
        partnerId: {type: Number, required: true},
        editable: {type: Boolean, default: false},
    },

    mixins: [deleteEntry],

    components: {
        FormText,
        FormString,
        FormDate,
        FormDictionary,
        FormNumber,
        FormPopUp,
        GuiValue,
        GuiHint,
        ListTableCell,
        ListTableRow,
        Pagination,
        GuiMessage,
        ListTable,
        GuiActionsMenu,
        DictionaryDropDown,
        InputDate,
        LayoutFiltersItem,
        LayoutFilters,
        GuiHeading,
        GuiContainer,
        LoadingProgress

    },

    data: () => ({
        list: list('/api/account'),
        limit_form: form(null, '/api/account/limit'),

        refill_form: form(null, '/api/account/refill'),
        withdrawal_form: form(null, '/api/account/withdrawal'),
        transaction: 0,
        has_reason: false,
        has_reason_date: false,
    }),

    created() {
        this.list.options = {id: this.partnerId};
        this.list.initial();
        this.limit_form.toaster = this.$toast;
        this.refill_form.toaster = this.$toast;
        this.withdrawal_form.toaster = this.$toast;
    },

    methods: {
        limitChange() {
            this.limit_form.reset();
            this.limit_form.set('limit', this.list.payload['limit'], 'required|numeric|bail', 'Допустимый остаток по счёту', true);
            this.limit_form.load();

            this.$refs.popup_limit.show()
                .then(result => {
                    this.list.payload['limit'] = result.payload['limit'];
                });
        },

        editRefill(transaction = null) {
            this.transaction = transaction ? transaction['id'] : 0;
            this.$store.dispatch('dictionary/refresh', 'transaction_refill_types')
                .then(() => {
                    this.refill_form.reset();
                    this.refill_form.set('type_id', transaction ? transaction['type_id'] : null, 'required', 'Способ пополнения', true);
                    this.refill_form.set('timestamp', transaction ? transaction['date'] : null, 'required', 'Дата операции', true);
                    this.refill_form.set('reason', transaction ? transaction['reason'] : null, null, 'Номер счёта', true);
                    this.refill_form.set('reason_date', transaction ? transaction['reason_date'] : null, null, 'Дата счёта', true);
                    this.refill_form.set('amount', transaction ? transaction['amount'] : null, 'required|numeric|min:1|bail', 'Сумма', true);
                    this.refill_form.set('comments', transaction ? transaction['comments'] : null, null, 'Комментарии', true);

                    if (transaction) {
                        this.typeChanged(transaction['type_id'])
                    }
                    this.refill_form.load();

                    this.$refs.popup_refill.show()
                        .then(() => {
                            this.list.load();
                        });
                });
        },

        editWithdrawal(transaction = null) {
            this.transaction = transaction ? transaction['id'] : 0;
            this.$store.dispatch('dictionary/refresh', 'transaction_refill_types')
                .then(() => {
                    this.withdrawal_form.reset();
                    this.withdrawal_form.set('type_id', 2, 'required', 'Способ пополнения', true);
                    this.withdrawal_form.set('timestamp', transaction ? transaction['date'] : null, 'required', 'Дата операции', true);
                    this.withdrawal_form.set('reason', transaction ? transaction['reason'] : null, 'required', 'Номер счёта', true);
                    this.withdrawal_form.set('reason_date', transaction ? transaction['reason_date'] : null, 'required', 'Дата счёта', true);
                    this.withdrawal_form.set('amount', transaction ? transaction['amount'] : null, 'required|numeric|min:1|bail', 'Сумма', true);
                    this.withdrawal_form.set('comments', transaction ? transaction['comments'] : null, null, 'Комментарии', true);

                    this.withdrawal_form.load();

                    this.$refs.popup_withdrawal.show()
                        .then(() => {
                            this.list.load();
                        });
                });
        },

        typeChanged(value) {
            let type = null;
            this.$store.getters['dictionary/dictionary']('transaction_refill_types').some(item => {
                if (item['id'] === value) {
                    type = item;
                    return true;
                }
                return false;
            });
            this.refill_form.set('reason', this.refill_form.values['reason'], type['has_reason'] ? 'required' : null, type['reason_title'], true);
            this.refill_form.set('reason_date', this.refill_form.values['reason_date'], type['has_reason_date'] ? 'required' : null, type['reason_date_title'], true);
            this.has_reason = Boolean(type['has_reason']);
            this.has_reason_date = Boolean(type['has_reason_date']);
        },

        remove(transaction) {
            this.deleteEntry(
                'Удалить операцию "' + transaction['type'] + '" от ' + transaction['date'] + '?',
                '/api/account/delete',
                {id: transaction.id})
                .then(() => {
                    this.list.load();
                });
        },
    }
}
</script>
