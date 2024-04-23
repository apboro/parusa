<template>
    <LayoutPage :loading="list.is_loading" :title="$route.meta['title']">
        <LayoutFilters>
            <template #search>
                <LayoutFiltersItem :title="'Поиск по ФИО и по ID'">
                    <InputSearch v-model="list.search" @change="list.load()"/>
                </LayoutFiltersItem>
            </template>
        </LayoutFilters>

        <ListTable v-if="list.list && list.list.length > 0" :titles="list.titles" :has-action="true">
            <ListTableRow v-for="partner in sortedList">
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
                    <GuiValue v-else>Смена открыта <br>{{ formatDate(partner.open_shift.start_at) }}</GuiValue>
                </ListTableCell>
            </ListTableRow>
        </ListTable>

        <GuiMessage v-else-if="list.is_loaded">Ничего не найдено</GuiMessage>

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
        promoterId: null,
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
        formatDate(dateString) {
            const parsedDate = new Date(dateString.replace(/-/g, '/'));
            const options = {
                weekday: 'short',
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: 'numeric',
                minute: 'numeric',
            };

            return new Intl.DateTimeFormat('ru-RU', options).format(parsedDate);
        },
        commissionPercent(partner) {
            return partner['open_shift']['tariff']['commission'] + partner['open_shift']['commission_delta'];
        },
        highlight(text) {
            return this.$highlight(text, this.list.search);
        },
        openShift(promoter) {
            this.$dialog.show('Открыть смену для "' + promoter.name + '"?', 'question', 'orange', [
                this.$dialog.button('yes', 'Продолжить', 'orange'),
                this.$dialog.button('no', 'Отмена', 'blue'),
            ]).then(result => {
                if (result === 'yes') {
                    axios.post('/api/terminals/promoters/open_work_shift', {promoterId: promoter.id})
                        .then(response => {
                            this.$toast.success(response.data['message']);
                            this.list.load()
                        })
                        .catch(error => {
                            this.$toast.error(error.response.data['message']);
                        })
                }
            });
        },
    }
}
</script>
