<template>
    <LayoutPage :loading="list.is_loading" :title="$route.meta['title']">
        <LayoutFilters>
            <LayoutFiltersItem :class="'w-25'" :title="'Дата'">
                <GuiIconButton :class="'mr-5'" :border="false" @click="setDay(-1)">
                    <IconBackward/>
                </GuiIconButton>
                <InputDate
                    :original="list.filters_original['date']"
                    v-model="list.filters['date']"
                    :pick-on-clear="false"
                    :small="true"
                    @change="dateChanged"
                    ref="date"
                />
                <GuiIconButton :class="'ml-5'" :border="false" @click="setDay(1)">
                    <IconForward/>
                </GuiIconButton>
            </LayoutFiltersItem>
            <LayoutFiltersItem :class="'w-25'" :title="'Тип программы'">
                <DictionaryDropDown
                    :dictionary="'excursion_programs'"
                    :fresh="true"
                    v-model="list.filters['program_id']"
                    :original="list.filters_original['program_id']"
                    :placeholder="'Все'"
                    :has-null="true"
                    :small="true"
                    @change="list.load()"
                />
            </LayoutFiltersItem>
            <LayoutFiltersItem :class="'w-25'" :title="'Экскурсия'">
                <DictionaryDropDown
                    :dictionary="'excursions'"
                    :fresh="true"
                    v-model="list.filters['excursion_id']"
                    :original="list.filters_original['excursion_id']"
                    :placeholder="'Все'"
                    :has-null="true"
                    :search="true"
                    :small="true"
                    @change="list.load()"
                />
            </LayoutFiltersItem>
            <LayoutFiltersItem :class="'w-25'" :title="'Причал отправления'">
                <DictionaryDropDown
                    :dictionary="'piers'"
                    :fresh="true"
                    v-model="list.filters['start_pier_id']"
                    :original="list.filters_original['start_pier_id']"
                    :placeholder="'Все'"
                    :has-null="true"
                    :search="true"
                    :right="true"
                    :small="true"
                    @change="list.load()"
                />
            </LayoutFiltersItem>
        </LayoutFilters>

        <ListTable v-if="list.list && list.list.length > 0" :titles="list.titles" :has-action="true">
            <ListTableRow v-for="(trip, key) in list.list" :key="key">
                <ListTableCell>
                    <div><b class="text-lg">{{ trip['start_time'] }}</b></div>
                    <div><span :style="{fontSize: '13px'}">{{ trip['start_date'] }}</span></div>
                </ListTableCell>
                <ListTableCell>
                    <div class="link" @click="excursionInfo(trip['excursion_id'])"><b>{{ trip['excursion'] }}</b></div>
                    <div><span :style="{fontSize: '13px'}">{{ trip['programs'] && trip['programs'].length ? trip['programs'].join(', ') : '' }}</span></div>
                </ListTableCell>
                <ListTableCell>
                    <span class="link" @click="pierInfo(trip['pier_id'])">{{ trip['pier'] }}</span>
                </ListTableCell>
                <ListTableCell>
                    {{ trip['tickets_total'] - trip['tickets_count'] }} ({{ trip['tickets_total'] }})
                </ListTableCell>
                <ListTableCell>
                    <table v-if="trip['rates'] && trip['rates'].length > 0" :style="{fontSize: '13px'}">
                        <tr v-for="rate in trip['rates']">
                            <td class="pr-15 no-wrap">{{ rate['name'] }}</td>
                            <td class="no-wrap">{{ rate['value'] }} руб.</td>
                        </tr>
                    </table>
                </ListTableCell>
                <ListTableCell>
                    <GuiButton @click="trip['excursion__only_parus'] ?null: addToOrder(trip)" :disabled="trip['excursion__only_parus']?true:false">Выбрать</GuiButton>
                </ListTableCell>
            </ListTableRow>
        </ListTable>

        <GuiMessage v-else-if="list.is_loaded">Ничего не найдено</GuiMessage>

        <Pagination :pagination="list.pagination" @pagination="(page, per_page) => list.load(page, per_page)"/>

        <TicketsSelect ref="select_popup"/>
        <ExcursionInfo ref="excursion_info"/>
        <PierInfo ref="pier_info"/>
    </LayoutPage>
</template>

<script>
import list from "@/Core/List";
import LayoutPage from "@/Components/Layout/LayoutPage";
import LayoutFilters from "@/Components/Layout/LayoutFilters";
import LayoutFiltersItem from "@/Components/Layout/LayoutFiltersItem";
import GuiIconButton from "@/Components/GUI/GuiIconButton";
import IconBackward from "@/Components/Icons/IconBackward";
import InputDate from "@/Components/Inputs/InputDate";
import IconForward from "@/Components/Icons/IconForward";
import DictionaryDropDown from "@/Components/Inputs/DictionaryDropDown";
import GuiMessage from "@/Components/GUI/GuiMessage";
import Pagination from "@/Components/Pagination";
import ListTable from "@/Components/ListTable/ListTable";
import ListTableRow from "@/Components/ListTable/ListTableRow";
import ListTableCell from "@/Components/ListTable/ListTableCell";
import GuiButton from "@/Components/GUI/GuiButton";
import TicketsSelect from "@/Pages/Partner/Parts/TicketsSelect";
import ExcursionInfo from "@/Pages/Partner/Parts/ExcursionInfo";
import PierInfo from "@/Pages/Partner/Parts/PierInfo";

export default {
    components: {
        PierInfo,
        ExcursionInfo,
        TicketsSelect,
        GuiButton,
        ListTableCell,
        ListTableRow,
        ListTable,
        Pagination,
        GuiMessage,
        DictionaryDropDown,
        IconForward,
        InputDate,
        IconBackward,
        GuiIconButton,
        LayoutFiltersItem,
        LayoutFilters,
        LayoutPage,
    },

    data: () => ({
        list: list('/api/trips/select'),
        form: null,
        popup_title: null,
    }),

    created() {
        this.list.initial();
    },

    methods: {
        dateChanged(value) {
            if (value !== null) {
                this.list.load();
            }
        },
        setDay(increment) {
            this.$refs.date.addDays(increment);
        },
        addToOrder(trip) {
            this.$refs.select_popup.handle(trip);
        },
        excursionInfo(excursion_id) {
            this.$refs.excursion_info.show(excursion_id);
        },
        pierInfo(pier_id) {
            this.$refs.pier_info.show(pier_id);
        },
    }
}
</script>
