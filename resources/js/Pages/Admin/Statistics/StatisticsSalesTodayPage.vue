<template>
    <LayoutPage :loading="list.is_loading" :title="$route.meta['title']">
        <div>
            <gui-message>
                Мобильные кассы
            </gui-message>
        </div>
        <ListTable v-if="list.is_loaded && list.data && list.data.terminals && list.data.terminals.length > 0"
                   :titles="['КАССА', 'СТАТУС', 'ПРИЧАЛ', 'АДРЕС', 'ВЫРУЧКА ЗА СЕГОДНЯ', 'ПРОДАНО БИЛЕТОВ', 'ПОСЛЕДНЯЯ ПРОДАЖА']">
            <ListTableRow v-for="terminal in list.data.terminals">
                <ListTableCell :nowrap="true">
                    <GuiActivityIndicator :active="terminal['active']"/>
                    <router-link class="link" :to="{ name: 'terminals-view', params: { id: terminal['id'] }}">Касса №{{ terminal['id'] }}</router-link>
                </ListTableCell>
                <ListTableCell>
                    {{ terminal['status'] }}
                </ListTableCell>
                <ListTableCell>
                    {{ terminal['pier'] }}
                </ListTableCell>
                <ListTableCell>
                    {{ terminal['place'] }}
                </ListTableCell>
                <ListTableCell>
                    <div style="margin-bottom: 5px;">приход общ.: {{ terminal['today_sold_amount'] }} руб.</div>
                    <div style="margin-bottom: 5px;">приход нал.: {{ terminal['today_sold_cash_amount'] }} руб.</div>
                    <div style="margin-bottom: 10px;">приход безнал.: {{ terminal['today_sold_card_amount'] }} руб.</div>
                    <div style="margin-bottom: 5px;">возврат нал.: {{ terminal['today_return_cash_amount'] }} руб.</div>
                    <div style="margin-bottom: 10px;">возврат безнал.: {{ terminal['today_return_card_amount'] }} руб.</div>
                    <div style="margin-bottom: 5px;">в кассе нал.: {{ terminal['today_total_cash_amount'] }} руб.</div>
                    <div style="margin-bottom: 10px;">на р/с: {{ terminal['today_total_card_amount'] }} руб.</div>
                    <div style="margin-bottom: 10px;"><b>итого: {{ terminal['today_total'] }} руб.</b></div>
                    <div class="text-gray" style="font-size: 11px; white-space: nowrap;">{{ terminal['period_start'] }} — {{ terminal['period_end'] }}</div>
                </ListTableCell>
                <ListTableCell>
                    {{ terminal['today_tickets_sold'] }}
                </ListTableCell>
                <ListTableCell>
                    {{ terminal['last_sale'] }}
                </ListTableCell>
            </ListTableRow>
        </ListTable>

        <GuiMessage border v-else-if="list.is_loaded">Ничего не найдено</GuiMessage>

        <gui-message>Витрина на сайте "Алые паруса"</gui-message>

        <ListTable v-if="list.is_loaded"
                   :titles="['СПОСОБ ПРОДАЖ', 'ВЫРУЧКА ЗА СЕГОДНЯ', 'ПРОДАНО БИЛЕТОВ', 'ПОСЛЕДНЯЯ ПРОДАЖА']">
            <ListTableRow>
                <ListTableCell>
                    Витрина на сайте "Алые паруса"
                </ListTableCell>
                <ListTableCell>
                    <div style="margin-bottom: 5px;">приход общ.: {{ list.data['site']['today_sold_amount'] }} руб.</div>
                    <div style="margin-bottom: 5px;">возврат общ.: {{ list.data['site']['today_return_amount'] }} руб.</div>
                    <div class="text-gray" style="font-size: 11px; white-space: nowrap;">{{ list.data['site']['period_start'] }} — {{ list.data['site']['period_end'] }}</div>
                </ListTableCell>
                <ListTableCell>
                    {{ list.data['site']['today_tickets_sold'] }}
                </ListTableCell>
                <ListTableCell>
                    {{ list.data['site']['last_sale'] }}
                </ListTableCell>
            </ListTableRow>
        </ListTable>
    </LayoutPage>
</template>

<script>
import LayoutPage from "@/Components/Layout/LayoutPage";
import GuiActionsMenu from "@/Components/GUI/GuiActionsMenu";
import LayoutFilters from "@/Components/Layout/LayoutFilters";
import LayoutFiltersItem from "@/Components/Layout/LayoutFiltersItem";
import DictionaryDropDown from "@/Components/Inputs/DictionaryDropDown";
import ListTable from "@/Components/ListTable/ListTable";
import ListTableRow from "@/Components/ListTable/ListTableRow";
import ListTableCell from "@/Components/ListTable/ListTableCell";
import GuiActivityIndicator from "@/Components/GUI/GuiActivityIndicator";
import GuiMessage from "@/Components/GUI/GuiMessage";
import Pagination from "@/Components/Pagination";
import data from "@/Core/Data";

export default {
    components: {
        LayoutPage,
        GuiActionsMenu,
        LayoutFilters,
        LayoutFiltersItem,
        DictionaryDropDown,
        ListTable,
        ListTableRow,
        ListTableCell,
        GuiActivityIndicator,
        Pagination,
        GuiMessage,
    },

    data: () => ({
        list: data('/api/statistics/today/list'),
    }),

    created() {
        this.list.load();
    },
}
</script>
