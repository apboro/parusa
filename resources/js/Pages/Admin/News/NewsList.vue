<script>
import LayoutPage from "@/Components/Layout/LayoutPage.vue";
import GuiActionsMenu from "@/Components/GUI/GuiActionsMenu.vue";
import LayoutFilters from "@/Components/Layout/LayoutFilters.vue";
import DictionaryDropDown from "@/Components/Inputs/DictionaryDropDown.vue";
import LayoutFiltersItem from "@/Components/Layout/LayoutFiltersItem.vue";
import list from "@/Core/List";
import ListTable from "@/Components/ListTable/ListTable.vue";
import Pagination from "@/Components/Pagination.vue";
import ListTableRow from "@/Components/ListTable/ListTableRow.vue";
import ListTableCell from "@/Components/ListTable/ListTableCell.vue";
import GuiMessage from "@/Components/GUI/GuiMessage.vue";
import GuiActivityIndicator from "@/Components/GUI/GuiActivityIndicator.vue";

export default {
    name: "NewsList",
    components: {
        GuiActivityIndicator,
        GuiMessage,
        ListTableCell,
        ListTableRow,
        Pagination,
        ListTable,
        LayoutFiltersItem, DictionaryDropDown, LayoutFilters, GuiActionsMenu, LayoutPage
    },
    data: () => ({
        list: list('/api/news'),
    }),
    methods: {
        copy(news) {
            this.$dialog.show('Копировать новость"' + news['title'] + '"?', 'question', 'orange', [
                this.$dialog.button('yes', 'Продолжить', 'orange'),
                this.$dialog.button('no', 'Отмена', 'blue'),
            ]).then(result => {
                if (result === 'yes') {
                    axios.post('/api/news/copy', {id: news['id']})
                        .then(response => {
                            this.$toast.success(response.data['message']);
                        })
                        .catch(error => {
                            this.$toast.error(error.response.data['message']);
                        })
                        .finally(this.list.load());
                }
            });
        },
        edit(news) {
            this.$router.push({name: 'news-edit', params: {id: news.id}})
        },
        view(news) {
            this.$router.push({name: 'news-view', params: {id: news.id}})
        },
    },
    created() {
        this.list.initial();
    },
}
</script>

<template>
    <LayoutPage :title="'Новости'">
        <template #actions>
            <GuiActionsMenu>
                <router-link class="link" :to="{ name: 'news-edit', params: { id: 0 }}">Добавить новость</router-link>
            </GuiActionsMenu>
        </template>

        <LayoutFilters>
            <LayoutFiltersItem :title="'Статус экскурсии'">
                <DictionaryDropDown
                    :dictionary="'news_statuses'"
                    v-model="list.filters['status_id']"
                    :original="list.filters_original['status_id']"
                    :placeholder="'Все'"
                    :has-null="true"
                    :small="true"
                    @change="list.load()"
                />
            </LayoutFiltersItem>
        </LayoutFilters>

        <ListTable :titles="list.titles" v-if="list.list && list.list.length > 0">
            <ListTableRow v-for="news in list.list">
                <ListTableCell :class="'w-40'">
                    <router-link class="link" :to="{ name: 'news-view', params: { id: news['id'] }}">
                        {{ news['title'] }}
                    </router-link>
                </ListTableCell>
                <ListTableCell :class="'w-30'">
                    {{ news['send_at'] ?? news['created_at'] }}
                </ListTableCell>
                <ListTableCell :class="'w-30'">
                    {{ news['recipient'] }}
                </ListTableCell>
                <ListTableCell :class="'w-20'">
                    {{ news['status'] }}
                </ListTableCell>
                <ListTableCell>
                    <GuiActionsMenu :title="null">
                        <div class="buttons-menu" v-if="news['send_at']">
                            <span class="link" @click="view(news)">Просмотр</span>
                            <span class="link" @click="copy(news)">Копировать</span>
                        </div>
                        <div v-else class="buttons-menu">
                            <span class="link" @click="view(news)">Просмотр</span>
                            <span class="link" @click="edit(news)">Редактировать</span>
                        </div>
                    </GuiActionsMenu>
                </ListTableCell>
            </ListTableRow>
        </ListTable>

        <GuiMessage border v-else-if="list.is_loaded">Ничего не найдено</GuiMessage>

        <Pagination :pagination="list.pagination" @pagination="(page, per_page) => list.load(page, per_page)"/>

    </LayoutPage>
</template>

<style scoped lang="scss">
.buttons-menu {
    display: flex;
    flex-direction: column;
}
</style>
