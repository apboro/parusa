<script>
import GuiActionsMenu from "@/Components/GUI/GuiActionsMenu.vue";
import LayoutPage from "@/Components/Layout/LayoutPage.vue";
import TripsList from "@/Pages/Admin/Trips/Parts/TripsList.vue";
import GuiHeading from "@/Components/GUI/GuiHeading.vue";
import LayoutRoutedTabs from "@/Components/Layout/LayoutRoutedTabs.vue";
import DeleteEntry from "@/Mixins/DeleteEntry.vue";
import data from "@/Core/Data";
import GuiValue from "@/Components/GUI/GuiValue.vue";
import GuiFilesList from "@/Components/GUI/GuiFilesList.vue";
import GuiContainer from "@/Components/GUI/GuiContainer.vue";
import GuiButton from "@/Components/GUI/GuiButton.vue";

export default {
    name: "NewsViewPage",
    components: {
        GuiButton,
        GuiContainer,
        GuiFilesList, GuiValue, LayoutRoutedTabs, GuiHeading, TripsList, LayoutPage, GuiActionsMenu},
    mixins: [DeleteEntry],

    data: () => ({
        data: data('/api/news/view'),
    }),

    computed: {
        newsId() {
            return Number(this.$route.params.id);
        },

        processing() {
            return this.deleting || this.data.loading;
        },
    },

    created() {
        this.data.load({id: this.newsId});
    },

    methods: {
        edit(){
            this.$router.push({name: 'news-edit', params: {id: this.newsId}});
        },
        deleteNews() {
            this.deleteEntry(`Удалить новость "${this.data.data['title']}"?`, '/api/news/delete', {id: this.newsId})
                .then(() => {
                    this.$router.push({name: 'news-list'});
                });
        },
        update(payload) {
            Object.keys(payload).map(key => {
                this.data.data[key] = payload[key];
            })
        },
        send(){
            this.$dialog.show('Отправить новость"' + this.data.data['title'] + '"?', 'question', 'orange', [
                this.$dialog.button('yes', 'Продолжить', 'orange'),
                this.$dialog.button('no', 'Отмена', 'blue'),
            ]).then(result => {
                if (result === 'yes') {
                    axios.post('/api/news/send', {id: this.newsId})
                        .then(response => {
                            this.$toast.success(response.data['message']);
                        })
                        .catch(error => {
                            this.$toast.error(error.response.data['message']);
                        })
                        .finally(this.data.load({id: this.newsId}));
                }
            });
        }
    }
}
</script>

<template>
    <LayoutPage :loading="processing" :title="data.data['title']"
                :breadcrumbs="[{caption: 'Новости', to: {name: 'news-list'}}]"
                :link="{name: 'news-list'}"
                :link-title="'К списку новостей'"
    >
        <template v-slot:actions>
            <GuiActionsMenu>
                <span class="link" @click="deleteNews">Удалить новость</span>
            </GuiActionsMenu>
        </template>
        <GuiValue :title="'Заголовок'">{{data.data['title']}}</GuiValue>
        <GuiValue :title="'Дата создания'">{{data.data['created_at']}}</GuiValue>
        <GuiValue :title="'Дата отправки'" v-if="data.data['send_at']">{{data.data['send_at']}}</GuiValue>
        <GuiValue :title="'Получатели'">{{data.data['recipient']}}</GuiValue>
        <GuiValue :title="'Статус'">{{data.data['status']}}</GuiValue>
        <p>Шаблон письма</p>
        <GuiContainer w-150 mt-30 inline pl-20 v-if="data.data['images'] && data.data['images'][0]">
            {{ data.data['description'] }}
            <div class="excursion-image-view">
                <img class="excursion-image-view__image" :src="data.data['images'][0]" :alt="data.data['title']"/>
            </div>
        </GuiContainer>

        <div class="button-container">
            <GuiButton color="green" @clicked="edit">Редактировать</GuiButton>
            <GuiButton @clicked="send">Отправить</GuiButton>
        </div>
    </LayoutPage>
</template>

<style scoped lang="scss">
.excursion-image-view {
    margin-top: 20px;
    height: 370px;
    display: flex;
    justify-content: center;
    align-items: center;

    &__image {
        max-width: 100%;
        max-height: 100%;
    }
}

.button-container {
    margin-top: 30px;
    text-align: right;
}
</style>
