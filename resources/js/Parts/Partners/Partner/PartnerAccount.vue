<template>
    <loading-progress :loading="processing">
        <container mt-15 mb-15 border p-20>
            <container inline mr-50>
                <heading text-md mb-10>Баланс лицевого счёта</heading>
                <heading>{{ list.payload['balance'] }} руб.</heading>
            </container>
            <container inline>
                <heading text-md mb-10>Доступный остаток по лицевому счёту</heading>
                <heading>{{ list.payload['limit'] }} руб.</heading>
                <span class="link text-sm flex mt-10" v-if="editable" @click="limitChange">Изменить</span>
            </container>
        </container>

        <div class="page__body-bar">
            <div class="page__body-bar-filters">
                <page-bar-item :title="'Период'">
                    <base-date-input
                        v-model="list.filters['date_from']"
                        :original="list.filters_original['date_from']"
                        @changed="reload"
                    />
                    <base-date-input
                        v-model="list.filters['date_to']"
                        :original="list.filters_original['date_to']"
                        @changed="reload"
                    />
                    <!-- <actions-menu :title="null"/> -->
                </page-bar-item>
                <page-bar-item :title="'Тип операции'">
                    <dictionary-drop-down
                        :dictionary="'transaction_primary_types'"
                        v-model="list.filters['transaction_type_id']"
                        :placeholder="'Все'"
                        :has-null="true"
                        :original="list.filters_original['transaction_type_id']"
                        @changed="reload"
                    />
                </page-bar-item>
            </div>
            <div class="page__body-bar-search">
                <actions-menu :class="'self-align-end'" :title="'Операции'" v-if="editable">
                    <span class="link" @click="editRefill(null)">Пополнение счёта</span>
                </actions-menu>
            </div>
        </div>


        <container>
            <base-table v-if="!empty(list.data)">
                <template v-slot:header>
                    <base-table-head :header="list.titles" :has-actions="editable"/>
                </template>
                <base-table-row v-for="(transaction, key) in list.data" :key="key">
                    <base-table-cell>{{ transaction['date'] }}</base-table-cell>
                    <base-table-cell>{{ transaction['type'] }}</base-table-cell>
                    <base-table-cell :nowrap="true" :class="{'text-dark-green': transaction['sign'] === 1, 'text-dark-red': transaction['sign'] === -1}"
                    >{{ transaction['sign'] === -1 ? '-' : '+' }} {{ transaction['amount'] }} руб.
                    </base-table-cell>
                    <base-table-cell>
                        <div :class="{'mb-10': transaction['comments']}" v-if="transaction['reason_title']">{{ transaction['reason_title'] }}</div>
                        <hint v-if="transaction['comments']">{{ transaction['comments'] }}</hint>
                    </base-table-cell>
                    <base-table-cell :nowrap="true">{{ transaction['operator'] }}</base-table-cell>
                    <base-table-cell v-if="editable">
                        <actions-menu :title="null" v-if="transaction['editable'] || transaction['deletable']">
                            <span class="link" v-if="transaction['editable']" @click="editRefill(transaction)">Редактировать</span>
                            <span class="link" v-if="transaction['deletable']" @click="remove(transaction)">Удалить</span>
                        </actions-menu>
                    </base-table-cell>
                </base-table-row>
            </base-table>
            <message v-else-if="list.loaded">Нет операций за выбранный период</message>
            <base-pagination :pagination="list.pagination" @pagination="setPagination"/>
        </container>


        <container mt-30>
            <value :class="'w-300px'" :title="'Сумма по выборке'">{{ list.payload['selected_total'] }} руб.</value>
            <value :class="'w-300px'" :title="'Сумма на странице'">{{ list.payload['selected_page_total'] }} руб.</value>
        </container>
        <container mt-30>
            <heading text-lg>Статистика по счёту за период <span class="bold">{{ list.filters['date_from'] }} - {{ list.filters['date_to'] }}</span></heading>
            <heading text-md mt-20>Состояние лицевого счёта:</heading>
            <container inline pr-40 pt-15>
                <value :class="'w-300px'" :title="'На начало периода'">{{ list.payload['period_start_amount'] }} руб.</value>
                <value :class="'w-300px'" :title="'На конец периода'">{{ list.payload['period_end_amount'] }} руб.</value>
                <value :class="'w-300px'" :title="'Сальдо'">{{ list.payload['period_income_amount'] }} руб.</value>
            </container>
            <container inline pr-40 pt-15>
                <value :class="'w-300px'" :title="'Сумма продаж'">{{ list.payload['period_sell_amount'] }} руб.</value>
                <value :class="'w-300px'" :title="'Начислено комиссионных'">{{ list.payload['period_commission_amount'] }} руб.</value>
                <value :class="'w-300px'" :title="'Состав продаж, заказы/билеты'">{{ list.payload['period_sell_orders'] }} / {{ list.payload['period_sell_tickets'] }}</value>
            </container>
        </container>

        <pop-up ref="popup_limit" v-if="editable"
                :title="'Лимит остатока по лицевому счёту'"
                :buttons="[{result: 'no', caption: 'Отмена', color: 'white'}, {result: 'yes', caption: 'OK', color: 'green'}]"
                :resolving="limitFormResolving"
                :manual="true"
        >
            <data-field-input :datasource="form" :name="'limit'" @changed="limitChanged"/>
        </pop-up>

        <pop-up ref="popup_refill" v-if="editable"
                :title="'Пополнение счёта'"
                :buttons="[{result: 'no', caption: 'Отмена', color: 'white'}, {result: 'yes', caption: 'OK', color: 'green'}]"
                :resolving="refillFormResolving"
                :manual="true"
        >
            <container w-500px>
                <data-field-dictionary-dropdown :datasource="form" :dictionary="'transaction_refill_types'" :name="'type_id'" @changed="typeChanged"/>
                <data-field-date-time :datasource="form" :name="'timestamp'" v-if="form.values['type_id'] !== null"/>
                <data-field-input :datasource="form" :name="'reason'" v-if="has_reason && form.values['type_id'] !== null"/>
                <data-field-date :datasource="form" :name="'reason_date'" v-if="has_reason_date && form.values['type_id'] !== null"/>
                <data-field-input :datasource="form" :name="'amount'" :type="'number'" v-if="form.values['type_id'] !== null"/>
                <data-field-text-area :datasource="form" :name="'comments'" v-if="form.values['type_id'] !== null"/>
            </container>
        </pop-up>
    </loading-progress>
</template>

<script>
import LoadingProgress from "@/Components/LoadingProgress";
import Container from "@/Components/GUI/GuiContainer";
import ActionsMenu from "@/Components/GUI/GuiActionsMenu";
import PageBarItem from "@/Layouts/Parts/PageBarItem";
import Heading from "@/Components/GUI/GuiHeading";
import BaseDateInput from "@/Components/Base/BaseDateInput";
import ValueArea from "@/Components/GUI/GuiValueArea";
import Value from "@/Components/GUI/GuiValue";
import DictionaryDropDown from "@/Components/Dictionary/DictionaryDropDown";
import listDataSource from "@/Helpers/Core/listDataSource";
import UseBaseTableBundle from "@/Mixins/UseBaseTableBundle";
import empty from "@/Mixins/empty";
import Message from "@/Components/GUI/GuiMessage";
import BasePagination from "@/Components/GUI/GuiPagination";
import PopUp from "@/Components/PopUp";
import DataFieldInput from "@/Components/DataFields/DataFieldInput";
import formDataSource from "@/Helpers/Core/formDataSource";
import DataFieldDictionaryDropdown from "@/Components/DataFields/DataFieldDictionaryDropdown";
import DataFieldTextArea from "@/Components/DataFields/DataFieldTextArea";
import DataFieldDate from "@/Components/DataFields/DataFieldDate";
import DataFieldDateTime from "@/Components/DataFields/DataFieldDateTime";
import deleteEntry from "@/Mixins/DeleteEntry";
import Hint from "@/Components/GUI/GuiHint";

export default {
    props: {
        partnerId: {type: Number, required: true},
        editable: {type: Boolean, default: false},
    },

    mixins: [UseBaseTableBundle, empty, deleteEntry],

    components: {
        Hint,
        DataFieldDateTime,
        DataFieldDate,
        DataFieldTextArea,
        DataFieldDictionaryDropdown,
        DataFieldInput,
        PopUp,
        BasePagination,
        Message,
        DictionaryDropDown,
        Value,
        ValueArea,
        BaseDateInput,
        Heading,
        PageBarItem,
        ActionsMenu,
        Container,
        LoadingProgress,
    },

    data: () => ({
        list: null,
        form: null,

        has_reason: false,
        has_reason_date: false,
    }),

    computed: {
        processing() {
            return this.list.loading;
        },
    },

    created() {
        this.list = listDataSource('/api/account', true, {id: this.partnerId});
        this.list.load(1, null, true);
    },

    methods: {
        reload() {
            this.list.reload();
        },
        setPagination(page, perPage) {
            this.list.load(page, perPage);
        },

        limitChanged(name, value) {
            if (!isNaN(Number(value))) {
                this.form.validate(name, Number(value));
            }
        },
        limitFormResolving(result) {
            return result !== 'yes' || this.form.validateAll();
        },
        limitAfterSave(payload) {
            this.list.payload['limit'] = payload['limit'];
            this.form.loaded = false;
            this.$refs.popup_limit.hide();
        },
        limitChange() {
            this.form = formDataSource(null, '/api/account/limit', {id: this.partnerId});

            this.form.setField('limit', this.list.payload['limit'], 'required|numeric|bail', 'Допустимый остаток по счёту', true);

            this.form.afterSave = this.limitAfterSave;
            this.form.toaster = this.$toast;
            this.form.loaded = true;

            this.$refs.popup_limit.show()
                .then(result => {
                    if (result === 'yes') {
                        this.$refs.popup_limit.process(true);
                        this.form.save();
                    } else {
                        this.$refs.popup_limit.hide();
                    }
                });
        },

        editRefill(transaction = null) {
            this.$store.dispatch('dictionary/refresh', 'transaction_refill_types')
                .then(() => {
                    this.form = formDataSource(null, '/api/account/refill', {
                        partnerId: this.partnerId,
                        transactionId: transaction ? transaction['id'] : 0,
                    });
                    this.form.toaster = this.$toast;
                    this.form.afterSave = this.refillSaved;

                    this.form.setField('type_id', transaction ? transaction['type_id'] : null, 'required', 'Способ пополнения', true);
                    this.form.setField('timestamp', transaction ? transaction['date'] : null, 'required', 'Дата операции', true);
                    this.form.setField('reason', transaction ? transaction['reason'] : null, null, 'Номер счёта', true);
                    this.form.setField('reason_date', transaction ? transaction['reason_date'] : null, null, 'Дата счёта', true);
                    this.form.setField('amount', transaction ? transaction['amount'] : null, 'required|numeric|gt:zero|bail', 'Сумма', true);
                    this.form.setField('comments', transaction ? transaction['comments'] : null, null, 'Комментарии', true);
                    this.form.setField('zero', 0, null, '0', true);
                    if (transaction) {
                        this.typeChanged('', transaction['type_id'])
                    }
                    this.form.loaded = true;
                    this.$refs.popup_refill.show()
                        .then(() => {
                            this.$refs.popup_refill.hide();
                        });
                });
        },

        refillFormResolving(result) {
            if (result !== 'yes') {
                return true;
            } else if (!this.form.validateAll()) {
                return false;
            }

            this.$refs.popup_refill.process(true);
            this.form.failedSave = () => {
                this.$refs.popup_refill.process(false);
            };
            this.form.save();
            return false;
        },

        refillSaved() {
            this.list.load();
            this.$refs.popup_refill.hide();
        },

        typeChanged(name, value) {

            let type = null;
            this.$store.getters['dictionary/dictionary']('transaction_refill_types').some(item => {
                if (item['id'] === value) {
                    type = item;
                    return true;
                }
                return false;
            });

            this.form.setField('reason', this.form.values['reason'], type['has_reason'] ? 'required' : null, type['reason_title'], true);
            this.form.setField('reason_date', this.form.values['reason_date'], type['has_reason_date'] ? 'required' : null, type['reason_date_title'], true);
            this.has_reason = Boolean(type['has_reason']);
            this.has_reason_date = Boolean(type['has_reason_date']);
        },

        remove(transaction) {
            this.deleteEntry(
                'Удалить операцию "' + transaction['type'] + '" от ' + transaction['date'] + '?',
                '/api/account/delete',
                {id: transaction.id})
                .then(() => {
                    this.reload();
                })
        },
    }
}
</script>
