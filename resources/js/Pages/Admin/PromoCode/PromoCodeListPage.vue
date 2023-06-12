<template>
    <LayoutPage :loading="list.is_loading" :title="$route.meta['title']">
        <template #actions>
            <GuiActionsMenu>
                <router-link class="link" :to="{ name: 'promo-code-edit', params: { id: 0 }}">Добавить промокод</router-link>
            </GuiActionsMenu>
        </template>

        <LayoutFilters>
            <LayoutFiltersItem :title="'Статус промокода'">
                <DictionaryDropDown
                    :dictionary="'promo_code_statuses'"
                    v-model="list.filters['status_id']"
                    :original="list.filters_original['status_id']"
                    :placeholder="'Все'"
                    :has-null="true"
                    :small="true"
                    @change="list.load()"
                />
            </LayoutFiltersItem>
        </LayoutFilters>

        <ListTable :titles="list.titles" v-if="list.list && list.list.length > 0" :has-action="true">
            <ListTableRow v-for="promoCode in list.list">
                    <ListTableCell>
                        <GuiActivityIndicator :active="promoCode['active']"/>
                        {{ promoCode['name'] }}
                    </ListTableCell>
                    <ListTableCell>
                        {{ promoCode['code'] }}
                    </ListTableCell>
                    <ListTableCell>
                        {{ promoCode['amount'] }}
                    </ListTableCell>
                    <ListTableCell>
                        {{ promoCode['purchases'] }}
                    </ListTableCell>
                    <ListTableCell>
                        {{ promoCode['status'] }}
                    </ListTableCell>
                    <ListTableCell>
                        <GuiActionsMenu :title="null">
                            <span class="link" @click="editStatus(promoCode)">{{ promoCode['status'] !== 'Действующий' ? 'Активировать' : 'Деактивировать' }}</span>
                        </GuiActionsMenu>
                    </ListTableCell>
            </ListTableRow>
        </ListTable>

        <GuiMessage border v-else-if="list.is_loaded">Ничего не найдено</GuiMessage>

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
import ListTableRow from "@/Components/ListTable/ListTableRow";
import ListTableCell from "@/Components/ListTable/ListTableCell";
import GuiActivityIndicator from "@/Components/GUI/GuiActivityIndicator";
import GuiMessage from "@/Components/GUI/GuiMessage";
import Pagination from "@/Components/Pagination";

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
        list: list('/api/promo-codes'),
    }),

    created() {
        this.list.initial();
    },

    methods: {
        editStatus(promoCode) {
            let title = promoCode['status'] !== 'Действующий' ? "Активировать промокод: ": "Деактивировать промокод: ";
            let btnText = promoCode['status'] !== 'Действующий' ? "Активировать": "Деактивировать";
            this.$dialog.show(`${title} "${promoCode['name']}"?`, 'question', 'red', [
                this.$dialog.button('no', 'Отмена', 'blue'),
                this.$dialog.button('yes', btnText, 'red'),
            ]).then(result => {
                if (result === 'yes') {
                    this.access_updating = true;
                    axios.post('/api/promo-code/status', {id: promoCode['id']})
                        .then(response => {
                            this.list.load();
                            this.$toast.success(response.data.message, 3000);
                        })
                        .catch(error => {
                            this.$toast.error(error.response.data.message, 5000);
                        });
                }
            });
        },
    }
}
</script>
