<template>
    <loading-progress :loading="processing">
        <heading mt-15>Активные<span class="link text-md fl-right" @click="attachItem">Добавить {{ data.payload['item_name'] }}</span></heading>

        <container w-100 mt-15 pv-10 ph-15 border v-if="!processing">
            <div class="drag-item__header" v-if="active.length !== 0">
                        <span class="drag-item__body-value" :class="'same-width-' + name" v-for="(type, name) in data.payload.fields"
                        >{{ data.payload['titles'][name] }}</span>
            </div>
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
                <div class="drag-item__body">
                        <span class="drag-item__body-value" :class="'same-width-' + name" v-for="(type, name) in data.payload.fields"
                        >{{ item[name] }}</span>
                </div>
                <div class="drag-item__actions">
                    <div class="drag-item__actions-button drag-item__actions-button-on" title="Отключить" @click="switchOff(item)">
                        <icon-toggle-on/>
                    </div>
                    <div class="drag-item__actions-button drag-item__actions-button-edit" title="Редактировать" @click="editItem(item)">
                        <icon-edit/>
                    </div>
                    <div class="drag-item__actions-button drag-item__actions-button-remove" title="Удалить" @click="deleteItem(item)">
                        <icon-cross/>
                    </div>
                </div>
            </div>
            <message v-if="active.length === 0">Нет активных элементов</message>
        </container>

        <heading mt-15>Неактивные</heading>
        <container w-100 mt-15 pv-10 ph-15 border v-if="!processing">
            <div class="drag-item__header" v-if="inactive.length !== 0">
                        <span class="drag-item__body-value" :class="'same-width-' + name" v-for="(type, name) in data.payload.fields"
                        >{{ data.payload['titles'][name] }}</span>
            </div>
            <div class="drag-item" v-for="(item, key) in inactive"
                 :key="key"
                 :class="{'drag-item__dragging': dragging !== null && item.id === dragging.id}"
            >
                <div class="drag-item__head drag-item__head-non-draggable">
                    <icon-grip-vertical/>
                </div>
                <div class="drag-item__body">
                        <span class="drag-item__body-value" :class="'same-width-' + name" v-for="(type, name) in data.payload.fields"
                        >{{ item[name] }}</span>
                </div>
                <div class="drag-item__actions">
                    <span class="drag-item__actions-button drag-item__actions-button-off" title="Включить" @click="switchOn(item)">
                        <icon-toggle-off/>
                    </span>
                    <span class="drag-item__actions-button drag-item__actions-button-edit" title="Редактировать" @click="editItem(item)">
                        <icon-edit/>
                    </span>
                    <span class="drag-item__actions-button drag-item__actions-button-remove" title="Удалить" @click="deleteItem(item)">
                        <icon-cross/>
                    </span>
                </div>
            </div>
            <message v-if="inactive.length === 0">Нет неактивных элементов</message>
        </container>

        <pop-up ref="dictionary_item_popup"
                :title="dictionary_item_title"
                :buttons="[{result: 'no', caption: 'Отмена', color: 'white'},{result: 'yes', caption: 'OK', color: 'green'}]"
                :resolving="dictionaryItemFormResolving"
                :manual="true"
        >
            <container w-600px>
                <template v-for="(type, name) in data.payload.fields">
                    <data-field-input v-if="type === 'number'" :datasource="form" :name="name" :type="'number'"/>
                    <data-field-input v-else :datasource="form" :name="name"/>
                </template>
            </container>
        </pop-up>
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
import DeleteEntry from "../../Mixins/DeleteEntry";
import PopUp from "../../Components/PopUp";
import DataFieldInput from "../../Components/DataFields/DataFieldInput";
import formDataSource from "../../Helpers/Core/formDataSource";
import {parseRules} from "../../Helpers/Core/validator/validator";

export default {
    components: {
        DataFieldInput,
        PopUp,
        Message,
        IconToggleOff,
        IconToggleOn,
        IconCross,
        IconEdit,
        IconGripVertical,
        Heading,
        LoadingProgress,
        Container,
    },

    mixins: [DeleteEntry],

    props: {
        dictionary: {type: String, required: true},
    },

    data: () => ({
        data: null,
        dragging: null,
        form: null,
        dictionary_item_title: null,
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
        this.form = formDataSource(null, '/api/dictionaries/update');
        this.form.toaster = this.$toast;
        this.form.afterSave = this.dictionaryItemAfterSave;
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
            this.form.titles = this.data.payload['titles'];
            this.form.valid = {};
            this.form.validation_errors = {};
            Object.keys(this.data.payload['titles']).map(key => {
                if (typeof this.data.payload['validation'][key] !== "undefined") {
                    this.form.validation_rules[key] = parseRules(this.data.payload['validation'][key]);
                } else {
                    this.form.validation_rules[key] = {};
                }
            });
            setTimeout(() => {
                this.sameWidths();
            }, 50);
        },

        sameWidths() {
            let keys = [];
            Object.keys(this.data.payload.fields).map(key => {
                keys.push('same-width-' + key);
            });
            keys.map(key => {
                const elements = this.$el.querySelectorAll('.' + key);
                for (let i = 0; i < elements.length; i++) {
                    elements[i].style.width = 'auto';
                }
                // Get the biggest element width
                let biggest = 0;
                for (let i = 0; i < elements.length; i++) {
                    const width = elements[i].clientWidth;
                    if (width > biggest) {
                        biggest = width;
                    }
                }
                // Set biggest width to all
                biggest += 10;
                for (let i = 0; i < elements.length; i++) {
                    elements[i].style.width = biggest + 'px';
                }
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
            event.dataTransfer.setData('text/plain', null);
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
        },

        deleteItem(item) {
            const id = item['id'];
            const name = item['name'];

            this.deleteEntry('Удалить "' + name + '"?', '/api/dictionaries/delete', {name: this.dictionary, id: id})
                .then(() => {
                    this.data.data = this.data.data.filter(item => item['id'] !== id);
                    this.$nextTick(() => {
                        this.sameWidths();
                    });
                });
        },

        dictionaryItemFormResolving(result) {
            return result !== 'yes' || this.form.validateAll();
        },

        dictionaryItemAfterSave(payload) {
            const id = payload['id'];
            let found = null;
            this.data.data.map((item, key) => {
                if (item['id'] === id) {
                    found = key;
                    return true;
                }
                return false;
            });
            if (found === null) {
                this.data.data.push(payload);
            } else {
                this.data.data[found] = payload;
            }

            this.form.loaded = false;
            this.$refs.dictionary_item_popup.hide();
            this.$nextTick(() => {
                this.sameWidths();
            });
        },

        attachItem() {
            this.form.values = {};
            this.form.originals = {};
            Object.keys(this.form.titles).map(key => {
                this.form.values[key] = null;
                this.form.originals[key] = null;
            });
            this.dictionary_item_title = 'Добавление элемента';
            this.form.loaded = true;

            this.$refs.dictionary_item_popup.show()
                .then(result => {
                    if (result === 'yes') {
                        this.$refs.dictionary_item_popup.process(true);
                        this.form.options = {name: this.dictionary, id: 0};
                        this.form.save();
                    } else {
                        this.form.loaded = false;
                        this.$refs.dictionary_item_popup.hide();
                    }
                });
        },

        editItem(item) {
            this.form.values = {};
            this.form.originals = {};
            Object.keys(this.form.titles).map(key => {
                this.form.values[key] = item[key];
                this.form.originals[key] = item[key];
            });
            this.dictionary_item_title = 'Изменение элемента';
            this.form.loaded = true;

            this.$refs.dictionary_item_popup.show()
                .then(result => {
                    if (result === 'yes') {
                        this.$refs.dictionary_item_popup.process(true);
                        this.form.options = {name: this.dictionary, id: item['id']};
                        this.form.save();
                    } else {
                        this.form.loaded = false;
                        this.$refs.dictionary_item_popup.hide();
                    }
                });
        },
    }
}
</script>
