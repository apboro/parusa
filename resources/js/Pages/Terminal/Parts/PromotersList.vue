<template>
    <LayoutPage :loading="list.is_loading" :title="$route.meta['title']">
        <LayoutFilters>
            <template #search>
                <LayoutFiltersItem :title="'Поиск по ФИО и по ID'">
                    <InputSearch v-model="list.search" @change="list.load()"/>
                </LayoutFiltersItem>
            </template>
        </LayoutFilters>

        <ListTable v-if="list.list && list.list.length > 0" :titles="list.titles">
            <ListTableRow v-for="partner in list.list">
                <ListTableCell :class="'w-35'">
                    <GuiActivityIndicator :active="partner['active']"/>
                    <router-link class="link" :to="{ name: 'terminal-promoters-view', params: { id: partner['id'] }}"
                                 v-html="highlight(partner['name'])"/>
                </ListTableCell>
                <ListTableCell :class="'w-25'">{{ partner['id'] }}</ListTableCell>
                <ListTableCell :class="'w-25'">{{ partner['balance'] }} руб.</ListTableCell>
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

export default {
    components: {
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
        promoterId: null,
    }),

    created() {
        this.list.initial();
    },

    methods: {
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
                .then(response => {
                    this.$emit('update', response.payload);
                })
        }
    },
}
</script>
