<template>
    <loading-progress :loading="processing">
        <heading mt-15>Активные</heading>
        <container w-100 mt-15 pv-10 ph-15 border v-if="!processing">
            <div @dragenter="dragenterSetActive">
                <div class="drag-item" v-for="(item, key) in active"
                     :key="key"
                     :class="{'drag-item__dragging': dragging !== null && item.id === dragging.id}"
                     draggable="true"
                     @dragstart="dragstart($event, item)"
                     @drop="drop($event, item)"
                     @dragenter="dragenter($event, item)"
                     @dragend="dragend($event, item)"
                >
                    <div class="drag-item__head">
                        <icon-grip-vertical/>
                    </div>
                    <div class="drag-item__body">{{ item['name'] }}</div>
                    <div class="drag-item__actions">
                    <span class="drag-item__actions-button drag-item__actions-button-on" title="Отключить" @click="switchOff(item)">
                        <icon-toggle-on/>
                    </span>
                        <span class="drag-item__actions-button drag-item__actions-button-edit" title="Редактировать">
                        <icon-edit/>
                    </span>
                        <span class="drag-item__actions-button drag-item__actions-button-remove" title="Удалить">
                        <icon-cross/>
                    </span>
                    </div>
                </div>
                <message v-if="active.length === 0">Нет активных элементов</message>
            </div>
        </container>
        <heading mt-15>Неактивные</heading>
        <container w-100 mt-15 pv-10 ph-15 border v-if="!processing">
            <div @dragenter="dragenterUnsetActive">
                <div class="drag-item" v-for="(item, key) in inactive"
                     :key="key"
                     :class="{'drag-item__dragging': dragging !== null && item.id === dragging.id}"
                     draggable="true"
                     @dragstart="dragstart($event, item)"
                     @drop="drop($event, item)"
                     @dragenter="dragenter($event, item)"
                     @dragend="dragend($event, item)"
                >
                    <div class="drag-item__head">
                        <icon-grip-vertical/>
                    </div>
                    <div class="drag-item__body">{{ item['name'] }}</div>
                    <div class="drag-item__actions">
                    <span class="drag-item__actions-button drag-item__actions-button-off" title="Включить" @click="switchOn(item)">
                        <icon-toggle-off/>
                    </span>
                        <span class="drag-item__actions-button drag-item__actions-button-edit" title="Редактировать">
                        <icon-edit/>
                    </span>
                        <span class="drag-item__actions-button drag-item__actions-button-remove" title="Удалить">
                        <icon-cross/>
                    </span>
                    </div>
                </div>
                <message v-if="inactive.length === 0">Нет неактивных элементов</message>
            </div>
        </container>
    </loading-progress>
</template>

<script>
import genericDataSource from "../../Helpers/Core/genericDataSource";
import Container from "../../Components/GUI/Container";
import LoadingProgress from "../../Components/LoadingProgress";
import Heading from "../../Components/GUI/Heading";
import IconGripVertical from "../../Components/Icons/IconGripVertical";
import IconEdit from "../../Components/Icons/IconEdit";
import IconCross from "../../Components/Icons/IconCross";
import IconToggleOn from "../../Components/Icons/IconToggleOn";
import IconToggleOff from "../../Components/Icons/IconToggleOff";
import Message from "../../Layouts/Parts/Message";

export default {
    components: {Message, IconToggleOff, IconToggleOn, IconCross, IconEdit, IconGripVertical, Heading, LoadingProgress, Container},
    props: {
        dictionary: {type: String, required: true},
    },

    data: () => ({
        data: null,
        dragging: null,
    }),

    watch: {
        dictionary(value) {
            this.init(value);
        },
    },

    computed: {
        processing() {
            return this.data === null || !!this.data.loading;
        },
        active() {
            return this.data.data.filter(item => Boolean(item.enabled) === true).sort((a, b) => a.order - b.order);
        },
        inactive() {
            return this.data.data.filter(item => Boolean(item.enabled) === false).sort((a, b) => a.order - b.order);
        },
    },

    created() {
        this.data = genericDataSource('/api/dictionaries/details');
        this.data.onLoad = this.loaded;
    },

    mounted() {
        this.init(this.dictionary);
    },

    methods: {
        init(dictionary) {
            this.data.load({name: dictionary});
        },

        loaded(data) {
            // fix order
            let order = 0;
            data.map(item => {
                item.order = order++;
            });
        },

        switchOff(item) {
            item['enabled'] = false;
            this.sync();
        },

        switchOn(item) {
            item['enabled'] = true;
            this.sync();
        },

        dragstart(event, item) {
            this.dragging = item;
            event.dataTransfer.effectAllowed = "move";
        },

        dragenter(event, item) {
            if (this.dragging === null) {
                return true;
            }
            if (item !== this.dragging) {
                const order = this.dragging.order;
                this.dragging.order = item.order;
                item.order = order;
            }
            event.preventDefault();
        },

        dragenterSetActive(event) {
            this.dragging.enabled = true;
            event.preventDefault();
        },

        dragenterUnsetActive(event) {
            this.dragging.enabled = false;
            event.preventDefault();
        },

        drop() {
            this.dragging = null;
            return true;
        },

        dragend() {
            this.dragging = null;
            this.sync();
            return true;
        },

        sync() {
            let data = [];
            this.data.data.map(item => {
                data.push({id: item.id, order: item.order, enabled: item.enabled});
            });

            axios.post('/api/dictionaries/sync', {name: this.dictionary, data: data})
                .catch(() => {
                    this.$toast.error('Произошла ошибка', 5000);
                    this.init(this.dictionary);
                });
        }
    }
}
</script>
