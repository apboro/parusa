<template>
    <div>
        <GuiContainer w-50 mt-30 inline>
            <GuiValue :title="'Название'">{{ data['name'] }}</GuiValue>
            <GuiValue :title="'Название для чека'">{{ data['name_receipt'] }}</GuiValue>
            <GuiValue :title="'Тип программы'">{{ data['programs'] ? data['programs'].join(', ') : '' }}</GuiValue>
            <GuiValue :title="'Продолжительность'"><span v-if="data['duration']">{{ data['duration'] }} минут</span></GuiValue>
            <GuiValue :title="'Статус'">
                <span class="link" v-if="editable" @click="statusChange"><GuiActivityIndicator :active="data['active']"/>{{ data['status'] }}</span>
                <span v-else><GuiActivityIndicator :active="data['active']"/>{{ data['status'] }}</span>
            </GuiValue>
            <GuiValue :title="'Эксклюзивная'">
                {{ data['only_site'] ? 'Продажа только на сайте Алые Паруса' : 'Нет' }}
            </GuiValue>
            <GuiValue :title="'Единый билет'">
                {{ data['is_single_ticket'] ? 'Да' : 'Нет' }}
            </GuiValue>
            <GuiValue title='Обратная экскурсия'>
                {{ data['reverse_excursion'] ?? 'Нет' }}
            </GuiValue>
            <GuiValue title='Тип экскурсии'>
                {{ data['excursion_type'] }}
            </GuiValue>
        </GuiContainer>

        <GuiContainer w-50 mt-30 inline pl-20 v-if="data['images'] && data['images'][0]">
            <div class="excursion-image-view">
                <img class="excursion-image-view__image" :src="data['images'][image_index]" :alt="data['name']"/>
            </div>
        </GuiContainer>

        <GuiContainer w-100 mt-30 v-if="data['images'] && data['images'].length > 1">
            <img class="excursion-image" v-for="(image, key) in data['images']"
                 :class="{'excursion-image__selected': key === image_index}"
                 :src="image"
                 :alt="data['name']"
                 @click="selectImage(key)"
            />
        </GuiContainer>

        <GuiContainer w-100 mt-30>
            <GuiValueArea :title="'Краткое описание экскурсии'" v-text="data['announce']"/>
            <GuiValueArea :title="'Полное описание экскурсии'" v-text="data['description']"/>
        </GuiContainer>

        <GuiContainer w-100 mt-30>
            <GuiValueArea :title="'Карта маршрута'">
                <template v-if="data['trip_images'] && data['trip_images'][0]">
                    <img class="w-100" :src="data['trip_images'][0]" :alt="data['name']"/>
                </template>
            </GuiValueArea>
        </GuiContainer>

        <GuiContainer mt-30 v-if="editable">
            <GuiButton @click="edit">Редактировать</GuiButton>
        </GuiContainer>

        <FormPopUp :title="'Статус экскурсии'"
                   :form="form"
                   :options="{id: excursionId}"
                   ref="popup"
        >
            <GuiContainer w-350px>
                <FormDictionary :form="form" :name="'value'" :dictionary="'excursion_statuses'" :fresh="true" :hide-title="true"/>
            </GuiContainer>
        </FormPopUp>
    </div>
</template>

<script>


import GuiContainer from "@/Components/GUI/GuiContainer";
import GuiValue from "@/Components/GUI/GuiValue";
import GuiActivityIndicator from "@/Components/GUI/GuiActivityIndicator";
import GuiValueArea from "@/Components/GUI/GuiValueArea";
import form from "@/Core/Form";
import GuiButton from "@/Components/GUI/GuiButton";
import FormPopUp from "@/Components/FormPopUp";
import FormDictionary from "@/Components/Form/FormDictionary";

export default {
    props: {
        excursionId: {type: Number, required: true},
        data: {type: Object},
        editable: {type: Boolean, default: false},
    },

    components: {
        FormDictionary,
        FormPopUp,
        GuiButton,
        GuiValueArea,
        GuiActivityIndicator,
        GuiValue,
        GuiContainer
    },

    data: () => ({
        form: form(null, '/api/excursions/properties'),
        image_index: 0,
    }),

    methods: {
        selectImage(key) {
            this.image_index = key;
        },
        edit() {
            this.$router.push({name: 'excursion-edit', params: {id: this.excursionId}});
        },
        statusChange() {
            this.form.reset();
            this.form.set('name', 'status_id');
            this.form.set('value', this.data['status_id'], 'required', 'Статус экскурсии', true);
            this.form.toaster = this.$toast;
            this.form.load();
            this.$refs.popup.show()
                .then(response => {
                    this.$emit('update', response.payload);
                })
        },
    }
}
</script>

<style lang="scss" scoped>
.excursion-image-view {
    height: 370px;
    display: flex;
    justify-content: center;
    align-items: center;

    &__image {
        max-width: 100%;
        max-height: 100%;
    }
}

.excursion-image {
    cursor: pointer;
    height: 150px;
    margin: 0 10px 10px 0;
    display: inline-block;
    vertical-align: top;
    box-sizing: border-box;
    border: 4px solid transparent;
    border-radius: 3px;

    &__selected {
        border-color: #0B68C2;
    }
}
</style>
