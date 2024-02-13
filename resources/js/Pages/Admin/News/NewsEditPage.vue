<script>
import LayoutPage from "@/Components/Layout/LayoutPage.vue";
import FormText from "@/Components/Form/FormText.vue";
import FormImages from "@/Components/Form/FormImages.vue";
import FormString from "@/Components/Form/FormString.vue";
import FormDropdown from "@/Components/Form/FormDropdown.vue";
import GuiButton from "@/Components/GUI/GuiButton.vue";
import Container from "@/Apps/PromoterApp.vue";
import GuiContainer from "@/Components/GUI/GuiContainer.vue";
import form from "@/Core/Form";


export default {
    name: "NewsEditPage",
    components: {GuiContainer, Container, GuiButton, FormDropdown, FormImages, FormText, FormString, LayoutPage},
    data: () => ({
        form: form('/api/news/get', '/api/news/update'),
    }),
    created() {
        this.form.toaster = this.$toast;
        this.form.load({id: this.newsId});
    },
    computed: {
        newsId() {
            return Number(this.$route.params.id);
        },
    },
    methods: {
        save() {
            console.log('save')
            if (!this.form.validate()) {
                return;
            }
            this.form.save({id: this.newsId})
                .then(response => {
                    if (this.newsId === 0) {
                        this.$router.push({name: 'news-view', params: {id: response.payload['id']}});
                    } else {
                        this.$router.push({name: 'news-view', params: {id: this.newsId}});
                    }
                })
        },
    }
}
</script>

<template>
    <LayoutPage :title="'Добавление новост1132и'">
        <GuiContainer mt-30>
            <FormString :form="form" name="title" title="Название"/>
            <FormText :form="form" name="description" title="Текст"/>
            <FormImages :form="form" name="image" title="Картинка"/>
            <FormDropdown disabled :form="form" name="recipients" model-value="Все партнеры" :title="'Получатель'"/>
        </GuiContainer>

        <div class="button-container">
            <GuiButton color="green" @ckick="save">Сохранить</GuiButton>
            <GuiButton>Отправить</GuiButton>
        </div>
    </LayoutPage>
</template>

<style scoped lang="scss">
.button-container {
    margin-top: 30px;
    text-align: right;
}

</style>
