<template>
    <LayoutPage :loading="list.is_loading" :title="$route.meta['title']">
        <template #actions>
            <GuiActionsMenu>
                <span class="link" @click="showCommissionChecks">Изменить ставку комиссии</span>
            </GuiActionsMenu>
        </template>
        <LayoutFilters>
            <LayoutFiltersItem>
                <GuiButton v-if="commissionChanging" @click="showCommissionPopup">Изменить ставку</GuiButton>
            </LayoutFiltersItem>
            <template #search>
                <LayoutFiltersItem :title="'Поиск по ФИО и по ID'">
                    <InputSearch v-model="list.search" @change="list.load()"/>
                </LayoutFiltersItem>
            </template>
        </LayoutFilters>

        <ListTable v-if="list.list && list.list.length > 0" :titles="list.titles">
            <ListTableRow v-for="partner in sortedList">
                <ListTableCell :class="'w-5'" v-if="commissionChanging">
                    <InputCheckbox v-model="checkedPromoters" :value="partner['id']"
                                   :disabled="!partner['open_shift']"/>
                </ListTableCell>
                <ListTableCell :class="'w-30'">
                    <GuiActivityIndicator :active="partner['active']"/>
                    <router-link class="link" :to="{ name: 'terminal-promoters-view', params: { id: partner['id'] }}"
                                 v-html="highlight(partner['name'])"/>
                </ListTableCell>
                <ListTableCell :class="'w-20'">{{ partner['id'] }}</ListTableCell>
                <ListTableCell :class="'w-20'">{{ partner['balance'] ?? 0 }} руб.</ListTableCell>
                <ListTableCell :class="'w-20'" v-if="!partner['open_shift']"></ListTableCell>
                <ListTableCell :class="'w-20'" v-if="partner['open_shift']">
                    <div style="font-weight: bold;">
                        {{ commissionPercent(partner) }}% - сейчас
                    </div>
                    <div v-if="partner['open_shift']['commission_delta'] !== 0">
                        {{
                            partner['open_shift']['tariff']['commission'] + '% '
                        }} - при открытии смены
                    </div>
                </ListTableCell>
                <ListTableCell :class="'w-15'">
                    <GuiButton v-if="!partner.open_shift" @click="openShift(partner)">открыть смену</GuiButton>
                    <GuiValue v-else>Смена открыта <br>{{ partner.open_shift.start_at }}</GuiValue>
                </ListTableCell>
            </ListTableRow>
        </ListTable>

        <GuiMessage v-else-if="list.is_loaded">Ничего не найдено</GuiMessage>

        <Pagination :pagination="list.pagination" @pagination="(page, per_page) => list.load(page, per_page)"/>

        <FormPopUp :title="'Открытие смены'"
                   :form="form"
                   :options="{promoterId: promoterId}"
                   ref="popup"
        >
            <GuiContainer w-350px>
                <FormDictionary :form="form" :name="'tariff_id'" :dictionary="'tariffs'" :fresh="true"
                                :hide-title="true" :placeholder="'Тариф'"/>
            </GuiContainer>
        </FormPopUp>
        <FormPopUp :title="'Изменение ставки комиссии'"
                   :form="formComm"
                   :options="{newCommValue: newCommValue, promotersIds: checkedPromoters}"
                   ref="commission_popup"
        >
            <GuiContainer w-350px>
                <InputDropDown :options="list.payload.tariffsCommissionsValues" v-model="newCommValue"
                               :placeholder="'Выберите новую ставку'"/>
            </GuiContainer>
        </FormPopUp>

    </LayoutPage>
</template>

<script>
import list from "@/Core/List";
import LayoutPage from "@/Components/Layout/LayoutPage.vue";
import GuiActionsMenu from "@/Components/GUI/GuiActionsMenu.vue";
import LayoutFilters from "@/Components/Layout/LayoutFilters.vue";
import LayoutFiltersItem from "@/Components/Layout/LayoutFiltersItem.vue";
import DictionaryDropDown from "@/Components/Inputs/DictionaryDropDown.vue";
import ListTable from "@/Components/ListTable/ListTable.vue";
import InputSearch from "@/Components/Inputs/InputSearch.vue";
import ListTableRow from "@/Components/ListTable/ListTableRow.vue";
import ListTableCell from "@/Components/ListTable/ListTableCell.vue";
import GuiAccessIndicator from "@/Components/GUI/GuiAccessIndicator.vue";
import GuiActivityIndicator from "@/Components/GUI/GuiActivityIndicator.vue";
import GuiMessage from "@/Components/GUI/GuiMessage.vue";
import Pagination from "@/Components/Pagination.vue";
import GuiButton from "@/Components/GUI/GuiButton.vue";
import GuiContainer from "@/Components/GUI/GuiContainer.vue";
import FormNumber from "@/Components/Form/FormNumber.vue";
import FormCheckBox from "@/Components/Form/FormCheckBox.vue";
import FormString from "@/Components/Form/FormString.vue";
import FormPopUp from "@/Components/FormPopUp.vue";
import FormText from "@/Components/Form/FormText.vue";
import form from "@/Core/Form";
import FormPhone from "@/Components/Form/FormPhone.vue";
import FormDictionary from "@/Components/Form/FormDictionary.vue";
import GuiValue from "@/Components/GUI/GuiValue.vue";
import InputNumber from "@/Components/Inputs/InputNumber.vue";
import CheckBox from "@/Components/Inputs/Helpers/CheckBox.vue";
import InputCheckbox from "@/Components/Inputs/InputCheckbox.vue";
import InputDropDown from "@/Components/Inputs/InputDropDown.vue";

export default {
    components: {
        InputDropDown,
        InputCheckbox,
        CheckBox,
        InputNumber,
        GuiValue,
        FormDictionary,
        FormPhone,
        FormText, FormPopUp, FormString, FormCheckBox, FormNumber, GuiContainer,
        GuiButton,
        Pagination,
        GuiMessage,
        GuiActivityIndicator,
        GuiAccessIndicator,
        ListTableCell,
        ListTableRow,
        InputSearch,
        ListTable,
        DictionaryDropDown,
        LayoutFiltersItem,
        LayoutFilters,
        GuiActionsMenu,
        LayoutPage
    },

    data: () => ({
        list: list('/api/promoters'),
        form: form(null, '/api/terminals/promoters/open_work_shift'),
        formComm: form(null, '/api/terminals/promoters/change_commissions'),
        promoterId: null,
        newCommValue: null,
        commissionChanging: false,
        checkedPromoters: [],
    }),

    created() {
        this.list.initial();
    },
    computed: {
        sortedList() {
            return this.list.list.sort(((a, b) => {
                if (a.open_shift !== null && b.open_shift === null) {
                    return -1;
                } else if (a.open_shift === null && b.open_shift !== null) {
                    return 1;
                } else {
                    return 0;
                }
            }))
        }
    },

    methods: {
        showCommissionChecks() {
            this.commissionChanging = true;
            this.checkedPromoters = this.list.payload.promotersWithOpenedShift
            this.list.titles = {
                'checked': 'Выбрать',
                'name': 'ФИО промоутера',
                'ID': 'ID',
                'balance': 'Баланс',
                'commission': 'Ставка',
                'action': ''
            }
        },
        showCommissionPopup() {
            this.formComm.reset();
            this.formComm.load();
            this.formComm.toaster = this.$toast;
            this.$refs.commission_popup.show().then(() => {
                this.list.reload();
                this.commissionChanging = false;
                this.checkedPromoters = [];
            });
        },
        commissionPercent(partner) {
            return partner['open_shift']['tariff']['commission'] + partner['open_shift']['commission_delta'];
        },
        highlight(text) {
            return this.$highlight(text, this.list.search);
        },
        openShift(promoter) {
            this.promoterId = promoter['id'];
            this.form.reset();
            this.form.set('name', 'tariff_id');
            this.form.toaster = this.$toast;
            this.form.load();
            this.$refs.popup.show()
                .then(() => {
                    this.list.reload();
                })
        },
    },
}
</script>
