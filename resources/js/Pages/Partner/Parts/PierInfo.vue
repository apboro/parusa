<template>
    <PopUp :title="title" :close-on-overlay="true" ref="popup">
        <div style="flex-shrink: 1; overflow: hidden; max-width: 1020px;">
            <ScrollBox :mode="'vertical'">
                <GuiValue :title="'Название'">{{ info.data['name'] }}</GuiValue>
                <GuiValue :title="'Адрес причала'">{{ info.data['address'] }}</GuiValue>
                <GuiValue :title="'Время работы'">{{ info.data['work_time'] }}</GuiValue>
                <GuiValue :title="'Телефон'">{{ info.data['phone'] }}</GuiValue>
                <GuiValue :title="'Координаты причала'" v-if="info.data['latitude'] && info.data['longitude']">{{ info.data['latitude'] }}, {{ info.data['longitude'] }}</GuiValue>
                <GuiValue :title="'Статус'">
                    <GuiActivityIndicator :active="info.data['active']"/>
                    {{ info.data['status'] }}
                </GuiValue>
                <GuiContainer mt-15 v-if="info.data['images'] && info.data['images'][0]">
                    <img class="w-100" :src="info.data['images'][0]" :alt="info.data['name']"/>
                </GuiContainer>
                <GuiValueArea :title="'Описание причала'" v-text="info.data['description']"/>
                <GuiValueArea :title="'Как добраться'" v-text="info.data['way_to']"/>
                <GuiValueArea :title="'Причал на карте'" v-if="info.data['map_images'] && info.data['map_images'][0]">
                    <img class="w-100" :src="info.data['map_images'][0]" :alt="info.data['name']"/>
                </GuiValueArea>
            </ScrollBox>
        </div>
    </PopUp>
</template>

<script>
import PopUp from "@/Components/PopUp";
import data from "@/Core/Data";
import GuiContainer from "@/Components/GUI/GuiContainer";
import GuiValue from "@/Components/GUI/GuiValue";
import GuiActivityIndicator from "@/Components/GUI/GuiActivityIndicator";
import GuiValueArea from "@/Components/GUI/GuiValueArea";
import ScrollBox from "@/Components/ScrollBox";

export default {
    components: {
        ScrollBox,
        GuiValueArea,
        GuiActivityIndicator,
        GuiValue,
        GuiContainer,
        PopUp,
    },

    computed: {
        title() {
            return 'Причал — ' + this.info.data['name'];
        },
    },

    data: () => ({
        info: data('/api/piers/info'),
    }),

    methods: {
        show(id) {
            this.info.reset();
            this.$refs.popup.show();
            this.$refs.popup.process(true);
            this.info.load({id: id})
                .then(() => {
                    this.$nextTick(() => {
                        this.$refs.popup.fixSize();
                    })
                })
                .catch(error => {
                    this.$toast.error(error['message']);
                })
                .finally(() => {
                    this.$refs.popup.process(false);
                })
        }
    }
}
</script>

