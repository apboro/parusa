<template>
    <LayoutPage :loading="list.is_loading" :title="$route.meta['title']">
        <template #actions>
            <GuiActionsMenu>
                <router-link class="link" :to="{ name: 'promoters-edit', params: { id: 0 }}">Добавить промоутера
                </router-link>
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
                <ListTableCell>
                    <GuiActivityIndicator :active="partner['active']"/>
                    <router-link class="link" :to="{ name: 'promoters-view', params: { id: partner['id'] }}"
                                 v-html="highlight(partner['name'])"/>
                </ListTableCell>
                <ListTableCell>{{ partner['id'] }}</ListTableCell>
                <ListTableCell>{{ partner['balance'] }} руб.</ListTableCell>
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
                <ListTableCell v-else>
                    {{ partner['promoter_commission_rate'] }} %
                </ListTableCell>
            </ListTableRow>
        </ListTable>

        <GuiMessage v-else-if="list.is_loaded">Ничего не найдено</GuiMessage>

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

        <Pagination :pagination="list.pagination" @pagination="(page, per_page) => list.load(page, per_page)"/>
    </LayoutPage>
</template>

<script>
import list from "@/Core/List";
import LayoutPage from "@/Components/Layout/LayoutPage";
import GuiActionsMenu from "@/Components/GUI/GuiActionsMenu";
import LayoutFilters from "@/Components/Layout/LayoutFilters";
import LayoutFiltersItem from "@/Components/Layout/LayoutFiltersItem";
import DictionaryDropDown from "@/Components/Inputs/DictionaryDropDown";
import ListTable from "@/Components/ListTable/ListTable";
import InputSearch from "@/Components/Inputs/InputSearch";
import ListTableRow from "@/Components/ListTable/ListTableRow";
import ListTableCell from "@/Components/ListTable/ListTableCell";
import GuiAccessIndicator from "@/Components/GUI/GuiAccessIndicator";
import GuiActivityIndicator from "@/Components/GUI/GuiActivityIndicator";
import GuiMessage from "@/Components/GUI/GuiMessage";
import Pagination from "@/Components/Pagination";
import GuiContainer from "@/Components/GUI/GuiContainer.vue";
import FormPopUp from "@/Components/FormPopUp.vue";
import InputDropDown from "@/Components/Inputs/InputDropDown.vue";
import form from "@/Core/Form";
import InputCheckbox from "@/Components/Inputs/InputCheckbox.vue";
import GuiButton from "@/Components/GUI/GuiButton.vue";

export default {
    components: {
        GuiButton,
        InputCheckbox,
        InputDropDown, FormPopUp, GuiContainer,
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
        formComm: form(null, '/api/promoters/change_commissions'),
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
            const sortedList = [...this.list.list];

            sortedList.sort((a, b) => {
                if (a.open_shift !== null && b.open_shift === null) {
                    return -1;
                } else if (a.open_shift === null && b.open_shift !== null) {
                    return 1;
                } else {
                    return 0;
                }
            });

            return sortedList;
        }
    },
    methods: {
        highlight(text) {
            return this.$highlight(text, this.list.search);
        },
        commissionPercent(partner) {
            return partner['open_shift']['tariff']['commission'] + partner['open_shift']['commission_delta'];
        },
        showCommissionChecks() {
            this.commissionChanging = true;
            this.checkedPromoters = this.list.payload.promotersWithOpenedShift
            this.list.titles = {
                'checked': 'Выбрать',
                'name': 'ФИО промоутера',
                'ID': 'ID',
                'balance': 'Баланс',
                'commission': 'Ставка',
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
    },
}
</script>
