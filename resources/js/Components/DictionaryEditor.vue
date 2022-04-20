<template>
    <LoadingProgress :loading="this.data.is_loading">

        <GuiHeading mt-15 pb-5>Активные
            <GuiButton :class="'fl-right'" @click="editItem(null)">Добавить {{ data.data['item_name'] }}</GuiButton>
        </GuiHeading>

        <GuiContainer w-100 mt-15 pv-10 ph-15 border v-if="data.is_loaded">
            <div class="drag-item__header" v-if="active.length !== 0">
                        <span class="drag-item__body-value" :class="[name !== data.data['auto'] ? 'same-width-' + name : 'drag-item__body-value-auto']"
                              v-for="name in displayableFields"
                        >{{ data.data['titles'][name] }}</span>
            </div>
            <div class="drag-item" v-for="(item, key) in active"
                 :key="key"
                 :class="{'drag-item__dragging': dragging !== null && item.id === dragging.id}"
                 draggable="true"
                 @dragstart="(event) => dragstart(event, item)"
                 @drop="(event) => drop(event, item)"
                 @dragenter="(event) => dragenter(event, item)"
                 @dragend="(event) => dragend(event, item)"
            >
                <div class="drag-item__head">
                    <IconGripVertical/>
                </div>
                <div class="drag-item__body">
                    <span class="drag-item__body-value" :class="[name !== data.data['auto'] ? 'same-width-' + name : 'drag-item__body-value-auto']"
                          v-for="name in displayableFields" v-html="format(item[name], name)"></span>
                </div>
                <div class="drag-item__actions">
                    <div class="drag-item__actions-button drag-item__actions-button-on" title="Отключить" @click="switchOff(item)">
                        <IconToggleOn/>
                    </div>
                    <div class="drag-item__actions-button drag-item__actions-button-edit" title="Редактировать" @click="editItem(item)">
                        <IconEdit/>
                    </div>
                    <div v-if="item.locked === true" class="drag-item__actions-button drag-item__actions-button-system"
                         title="Системная запись">
                        <IconLock/>
                    </div>
                    <div v-else class="drag-item__actions-button drag-item__actions-button-remove" title="Удалить" @click="deleteItem(item)">
                        <IconCross/>
                    </div>
                </div>
            </div>
            <GuiMessage v-if="active.length === 0">Нет активных элементов</GuiMessage>
        </GuiContainer>

        <GuiHeading mt-15>Неактивные</GuiHeading>
        <GuiContainer w-100 mt-15 pv-10 ph-15 border v-if="data.is_loaded">
            <div class="drag-item__header" v-if="inactive.length !== 0">
                <span class="drag-item__body-value" :class="[name !== data.data['auto'] ? 'same-width-' + name : 'drag-item__body-value-auto']"
                      v-for="name in displayableFields">{{ data.data['titles'][name] }}</span>
            </div>
            <div class="drag-item" v-for="(item, key) in inactive"
                 :key="key"
                 :class="{'drag-item__dragging': dragging !== null && item.id === dragging.id}"
            >
                <div class="drag-item__head drag-item__head-non-draggable">
                    <icon-grip-vertical/>
                </div>
                <div class="drag-item__body">
                    <span class="drag-item__body-value" :class="[name !== data.data['auto'] ? 'same-width-' + name : 'drag-item__body-value-auto']"
                          v-for="name in displayableFields" v-html="format(item[name], name)"></span>
                </div>
                <div class="drag-item__actions">
                    <div class="drag-item__actions-button drag-item__actions-button-off" title="Включить" @click="switchOn(item)">
                        <IconToggleOn/>
                    </div>
                    <div class="drag-item__actions-button drag-item__actions-button-edit" title="Редактировать" @click="editItem(item)">
                        <IconEdit/>
                    </div>
                    <div v-if="item.locked === true" class="drag-item__actions-button drag-item__actions-button-system"
                         title="Системная запись">
                        <IconLock/>
                    </div>
                    <div v-else class="drag-item__actions-button drag-item__actions-button-remove" title="Удалить" @click="deleteItem(item)">
                        <IconCross/>
                    </div>
                </div>
            </div>
            <GuiMessage v-if="inactive.length === 0">Нет неактивных элементов</GuiMessage>
        </GuiContainer>

        <FormPopUp :title="dictionary_item_title"
                   :form="form"
                   ref="dictionary_item_popup"
        >
            <GuiContainer w-600px>
                <template v-for="(type, name) in data.data['fields']">
                    <FormNumber v-if="type === 'number'" :form="form" :name="name"/>
                    <FormText v-else-if="type === 'text'" :form="form" :name="name"/>
                    <FormCheckBox v-else-if="type === 'bool'" :form="form" :name="name"/>
                    <FormString v-else :form="form" :name="name"/>
                </template>
            </GuiContainer>
        </FormPopUp>
    </LoadingProgress>
</template>

<script>
import LoadingProgress from "@/Components/LoadingProgress";
import GuiHeading from "@/Components/GUI/GuiHeading";
import GuiContainer from "@/Components/GUI/GuiContainer";
import IconGripVertical from "@/Components/Icons/IconGripVertical";
import IconToggleOn from "@/Components/Icons/IconToggleOn";
import IconEdit from "@/Components/Icons/IconEdit";
import IconLock from "@/Components/Icons/IconLock";
import IconCross from "@/Components/Icons/IconCross";
import GuiMessage from "@/Components/GUI/GuiMessage";
import DeleteEntry from "@/Mixins/DeleteEntry";
import data from "@/Core/Data";
import GuiButton from "@/Components/GUI/GuiButton";
import FormPopUp from "@/Components/FormPopUp";
import form from "@/Core/Form";
import FormNumber from "@/Components/Form/FormNumber";
import FormText from "@/Components/Form/FormText";
import FormString from "@/Components/Form/FormString";
import FormCheckBox from "@/Components/Form/FormCheckBox";

export default {
    components: {
        FormCheckBox,
        FormString,
        FormText,
        FormNumber,
        FormPopUp,
        GuiButton,
        GuiMessage,
        IconCross,
        IconLock,
        IconEdit,
        IconToggleOn,
        IconGripVertical,
        GuiContainer,
        GuiHeading,
        LoadingProgress

    },

    mixins: [DeleteEntry],

    props: {
        dictionary: {type: String, required: true},
    },

    data: () => ({
        data: data('/api/dictionaries/details'),
        dragging: null,
        form: form(null, '/api/dictionaries/update'),
        dictionary_item_title: null,
    }),

    watch: {
        dictionary(value) {
            this.init(value);
        },
    },

    computed: {
        active() {
            if (!this.data.is_loaded) {
                return [];
            }
            return this.data.data['items'].filter(item => Boolean(item.enabled) === true).sort((a, b) => a.order - b.order);
        },
        inactive() {
            if (!this.data.is_loaded) {
                return [];
            }
            return this.data.data['items'].filter(item => Boolean(item.enabled) === false).sort((a, b) => a.order - b.order);
        },
        displayableFields() {
            if (!this.data.is_loaded) {
                return [];
            }
            const keys = Object.keys(this.data.data['fields']);
            if (typeof this.data.data['hide'] === "undefined" || this.data.data['hide'] === null) {
                return keys;
            }
            return keys.filter(key => this.data.data['hide'].indexOf(key) === -1)
        },
    },

    created() {
        this.form.toaster = this.$toast;
    },

    mounted() {
        this.init(this.dictionary);
    },

    methods: {
        init(dictionary) {
            this.data.load({name: dictionary})
                .then(result => {
                    let order = 0;
                    result.data['items'].map(item => {
                        item.order = order++;
                    });
                    setTimeout(() => {
                        this.sameWidths();
                    }, 50);
                });
        },

        format(value, name) {
            if (value === null) return null;
            if (typeof this.data.data['fields'][name] !== "undefined" && this.data.data['fields'][name] === 'bool') {
                return Boolean(value) ? 'да' : 'нет';
            }
            return String(value).replaceAll("\n", '<br/>');
        },

        sameWidths() {
            let keys = [];
            Object.keys(this.data.data['fields']).map(key => {
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
            this.$nextTick(() => {
                this.sameWidths();
            })
        },

        switchOn(item) {
            item['enabled'] = true;
            this.sync();
            this.$nextTick(() => {
                this.sameWidths();
            })
        },

        dragstart(event, item) {
            this.dragging = item;
            event.dataTransfer.setData('text/plain', item['id']);
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
            this.data.data['items'].map(item => {
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
                    this.data.data['items'] = this.data.data['items'].filter(item => item['id'] !== id);
                    this.$nextTick(() => {
                        this.sameWidths();
                    });
                });
        },

        editItem(item) {
            this.dictionary_item_title = item === null ? 'Добавление элемента' : 'Изменение элемента';
            this.form.reset();
            Object.keys(this.data.data['fields']).map(key => {
                this.form.set(key, item === null ? null : item[key], this.data.data['validation'][key], this.data.data['titles'][key], true);
            });
            this.form.load();

            this.$refs.dictionary_item_popup.show({name: this.dictionary, id: item === null ? 0 : item['id']})
                .then(result => {
                    const id = result.payload['id'];
                    let found = null;
                    this.data.data['items'].map((item, key) => {
                        if (item['id'] === id) {
                            found = key;
                            return true;
                        }
                        return false;
                    });
                    if (found === null) {
                        this.data.data['items'].push(result.payload);
                    } else {
                        this.data.data['items'][found] = result.payload;
                    }
                    this.$nextTick(() => {
                        this.sameWidths();
                    });
                });
        },
    }
}
</script>

<style lang="scss" scoped>
@use "sass:math";

$project_font: -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica Neue, Arial, Noto Sans, sans-serif, Apple Color Emoji, Segoe UI Emoji, Segoe UI Symbol, Noto Color Emoji !default;
$animation_time: 150ms !default;
$animation: cubic-bezier(0.24, 0.19, 0.28, 1.29) !default;
$base_size_unit: 35px !default;
$base_primary_color: #0D74D7 !default;
$base_primary_hover_color: lighten(#0D74D7, 10%) !default;
$base_red_color: #EA1B00 !default;
$base_red_hover_color: lighten(#EA1B00, 5%) !default;
$base_green_color: #00AF2E !default;
$base_green_hover_color: lighten(#00AF2E, 5%) !default;
$base_light_gray_color: #e5e5e5 !default;
$base_lightest_gray_color: #f7f7f7 !default;
$base_black_color: #1e1e1e !default;
$base_text_gray_color: #3f3f3f !default;
$base_gray_color: #8f8f8f !default;

.drag-item {
    display: flex;
    width: 100%;
    min-height: $base_size_unit;
    margin: 5px 0;
    box-sizing: border-box;
    border: 1px solid $base_light_gray_color;
    border-radius: 2px;
    background-color: $base_lightest_gray_color;

    &__dragging {
        background-color: transparent;
    }

    &__head {
        display: inline-flex;
        width: $base_size_unit;
        justify-content: center;
        align-items: center;
        cursor: grab;
        color: $base_text_gray_color;
        flex-grow: 0;
        flex-shrink: 0;
        //@include noSelection;

        &-non-draggable {
            cursor: default;
            color: $base_gray_color;
        }

        & > * {
            height: math.div($base_size_unit, 2);
        }
    }

    &__body, &__header {
        flex-grow: 1;
        flex-shrink: 0;
        font-size: 14px;
        font-family: $project_font;
        box-sizing: border-box;
        padding: 5px 15px 5px 0;
        color: $base_black_color;
        display: flex;

        &-value {
            margin: 0 50px 0 10px;
            padding: 5px 5px 5px 0;
            min-width: 50px;
            display: inline-block;

            &-auto {
                flex: 1;
                width: min-content;
                word-break: break-all;
            }
        }
    }

    &__header {
        padding-left: $base_size_unit;
        color: $base_text_gray_color;
    }

    &__actions {
        flex-grow: 0;
        flex-shrink: 0;
        display: inline-flex;

        &-button {
            display: flex;
            align-items: center;
            justify-content: center;
            width: $base_size_unit;
            cursor: pointer;
            transition: color $animation $animation_time;
            color: $base_text_gray_color;

            &-on {
                color: $base_green_color;

                &:hover {
                    color: $base_green_hover_color;
                }
            }

            &-off {
                color: $base_red_color;

                &:hover {
                    color: $base_red_hover_color;
                }
            }

            &-edit {
                color: $base_primary_color;

                &:hover {
                    color: $base_primary_hover_color;
                }
            }

            &-remove {
                color: $base_red_color;

                &:hover {
                    color: $base_red_hover_color;
                }
            }

            & > * {
                width: $base_size_unit * 0.5;
                height: $base_size_unit * 0.5;
            }
        }
    }
}
</style>
