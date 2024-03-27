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
import FormDictionary from "@/Components/Form/FormDictionary.vue";
import config from '@/config/env';
import Editor from "../../../../ckeditor5/build/ckeditor";

export default {
    name: "NewsEditPage",
    components: {
        FormDictionary,
        GuiContainer, Container, GuiButton, FormDropdown, FormImages, FormText, FormString, LayoutPage},
    data: () => ({
        form: form('/api/news/get', '/api/news/update'),
        editor: Editor,
        editorConfig: {
            toolbar: [
                'heading',
                '|', 'fontfamily', 'fontsize', 'fontColor', 'fontBackgroundColor',
                '|', 'bold', 'italic',
                '|', 'link', 'uploadImage', 'blockQuote',
                '|', 'bulletedList', 'numberedList', 'outdent', 'indent',
                '|', 'alignment',
                '|', 'insertTable',
                '|', 'undo', 'redo',
            ],
            simpleUpload: {
                uploadUrl: config.crm_url+'/news/images',
            },
        }
    }),
    created() {
        this.form.toaster = this.$toast;
        this.form.load({id: this.newsId});
    },
    computed: {
        newsId() {
            return Number(this.$route.params.id);
        },
        processing() {
            return this.form.loading || this.form.saving;
        },
    },
    methods: {
        save() {
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
        cancel(){
            if (this.newsId === 0){
                this.$router.push({name: 'news-list'})
            } else {
                this.$router.push({name: 'news-view', params: {id: this.newsId}})
            }
        }
    }
}
</script>

<template>
    <LayoutPage :loading="processing" :title="form.payload['name']"
                :breadcrumbs="[{caption: 'Новости', to: {name: 'news-list'}}]"
                :link="{name: 'news-list'}"
                :link-title="'К списку новостей'">
        <GuiContainer mt-30>
            <FormString style="margin-bottom: 5px" :form="form" name="title" title="Заголовок" />
            <ckeditor :editor="editor" v-model="form.values.description" :config="editorConfig"></ckeditor>
            <FormDictionary disabled :form="form" :dictionary="'news_recipients'" :name="'recipients'"/>
        </GuiContainer>

        <div class="button-container">
            <GuiButton color="green" @clicked="save">Сохранить</GuiButton>
            <GuiButton color="red" @clicked="cancel">Отмена</GuiButton>
        </div>
    </LayoutPage>
</template>

<style scoped lang="scss">
.button-container {
    margin-top: 30px;
    text-align: right;
}
</style>
