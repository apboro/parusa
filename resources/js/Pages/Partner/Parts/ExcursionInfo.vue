<template>
    <PopUp :title="title" :close-on-overlay="true" ref="popup">
        <div class="w-600px" style="flex-shrink: 1; overflow: hidden;">
            <ScrollBox :mode="'vertical'">
                <GuiValue :title="'Название'">{{ info.data['name'] }}</GuiValue>
                <GuiValue :title="'Тип программы'">{{ info.data['programs'] ? info.data['programs'].join(', ') : '' }}</GuiValue>
                <GuiValue :title="'Продолжительность'"><span v-if="info.data['duration']">{{ info.data['duration'] }} минут</span></GuiValue>
                <GuiValue :title="'Статус'">
                    <GuiActivityIndicator :active="info.data['active']"/>
                    {{ info.data['status'] }}
                </GuiValue>
                <GuiContainer mt-15 v-if="info.data['images'] && info.data['images'][0]">
                    <img class="w-100" :src="info.data['images'][0]" :alt="info.data['name']"/>
                </GuiContainer>
                <GuiValueArea :title="'Краткое описание экскурсии'" v-text="info.data['announce']"/>
                <GuiValueArea :title="'Полное описание экскурсии'" v-text="info.data['description']"/>
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
            return 'Экскурсия — ' + this.info.data['name'];
        },
    },

    data: () => ({
        info: data('/api/excursions/info'),
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

