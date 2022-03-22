<template>
    <InputWrapper class="input-images" :label="false" :dirty="isDirty" :disabled="disabled" :valid="valid">
        <div class="input-images__container">
            <ImagesImage v-for="(image, key) in modelValue"
                         :key="key"
                         :index="key"
                         :image="image"
                         @discard="discard"
            ></ImagesImage>
            <label class="input-images__add" v-if="canAdd">
                <IconPlusCircle :class="'input-images__add-icon'"/>
                <template v-if="canAddCount === 1">
                    <input class="input-images__add-input" type="file" accept="image/*" @change="handleFile">
                </template>
                <template v-else>
                    <input class="input-images__add-input" type="file" accept="image/*" multiple @change="handleFile">
                </template>
            </label>
        </div>
    </InputWrapper>
</template>

<script>
import clone from "@/Core/Helpers/Clone";
import InputWrapper from "@/Components/Inputs/Helpers/InputWrapper";
import ImagesImage from "@/Components/Inputs/Helpers/ImagesImage";
import IconPlusCircle from "@/Components/Icons/IconPlusCircle";

export default {
    components: {ImagesImage, InputWrapper, IconPlusCircle},
    props: {
        name: String,
        modelValue: {type: Array, default: null},
        original: {type: Array, default: null},
        valid: {type: Boolean, default: true},
        disabled: {type: Boolean, default: false},

        maxImages: {type: Number, default: 0},
    },

    emits: ['update:modelValue', 'change'],

    computed: {
        imagesCount() {
            return this.modelValue.length;
        },
        canAdd() {
            return this.maxImages === 0 || this.imagesCount < this.maxImages;
        },
        canAddCount() {
            return this.maxImages === 0 ? 0 : this.maxImages - this.imagesCount;
        },
        isDirty() {
            return JSON.stringify(this.original) !== JSON.stringify(this.modelValue);
        },
    },

    methods: {
        handleFile(e) {
            this.processFiles(e.target.files);
            e.target.value = '';
        },

        processFiles(files) {
            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                if (!file.type.startsWith('image/')) {
                    continue;
                }
                let val = {
                    name: file.name,
                    type: file.type,
                    size: file.size,
                    width: null,
                    height: null,
                    content: null,
                }

                const reader = new FileReader();
                reader.onload = (val => {
                    return e => {
                        val.content = e.target.result;
                        if (this.canAdd) {
                            let value = clone(this.modelValue);
                            value.push(val);
                            this.$emit('update:modelValue', value);
                            this.$emit('change', value, this.name);
                        }
                    }
                })(val);

                reader.readAsDataURL(file);
            }
        },

        discard(index) {
            let value = clone(this.modelValue);
            value.splice(index, 1);
            this.$emit('update:modelValue', value);
            this.$emit('change', value, this.name);
        },
    }
}
</script>

<style lang="scss">
@import "../variables";

$base_size_unit: 35px !default;
$animation_time: 150ms !default;
$animation: cubic-bezier(0.24, 0.19, 0.28, 1.29) !default;
$input_background_color: #ffffff !default;
$input_active_color: #0f82f1 !default;
$input_hover_color: #6fb4f7 !default;

.input-images {
    &__add {
        display: inline-flex;
        position: relative;
        vertical-align: top;
        width: $base_size_unit * 4;
        height: $base_size_unit * 4;
        box-sizing: border-box;
        padding: 5px;
        margin: 5px;
        justify-content: center;
        align-items: center;
        border: 1px solid $input_active_color;
        background-color: $input_background_color;
        color: $input_active_color;
        border-radius: 2px;
        cursor: pointer;
        transition: color $animation $animation_time, border-color $animation $animation_time;

        &:hover {
            border-color: $input_hover_color;
            color: $input_hover_color;
        }

        &-icon {
            width: 50%;
            height: 50%;
            color: inherit;
        }

        &-input {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            -moz-opacity: 0;
            filter: alpha(opacity=0);
            opacity: 0;
            font-size: 150px;
            height: 30px;
            z-index: -1;
        }
    }
}
</style>
