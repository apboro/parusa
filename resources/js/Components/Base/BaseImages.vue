<template>
    <div class="base-images"
         :class="{'base-images__not-valid': !valid, 'base-images__differs': changed}"
    >
        <div class="base-images__container">
            <base-images-image v-for="(image, key) in modelValue"
                               :key="key"
                               :index="key"
                               :image="image"
                               @discard="discard"
            ></base-images-image>
        </div>
        <label class="base-images__target" :class="{'base-images__target-disabled': !canAdd}">
            <template v-if="canAdd && canAddCount === 1">
                <input class="base-images__target-input" type="file" accept="image/*"
                       @change="handleFile">
            </template>
            <template v-else-if="canAdd">
                <input class="base-images__target-input" type="file" accept="image/*" multiple
                       @change="handleFile">
            </template>
            <span class="base-images__target-caption">Добавить</span>
        </label>
    </div>
</template>

<script>
import clone from "../../Helpers/Lib/clone";
import BaseImagesImage from "./Parts/BaseImagesImage";

export default {
    components: {BaseImagesImage},
    props: {
        modelValue: {type: Array, default: () => ([])},
        name: String,
        original: {type: Array, default: () => ([])},

        required: {type: Boolean, default: false},
        disabled: {type: Boolean, default: false},
        valid: {type: Boolean, default: true},

        maxImages: {type: Number, default: 0},
    },

    emits: ['update:modelValue', 'changed'],

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
        changed() {
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
                        console.log(e);
                        if (this.canAdd) {
                            let value = clone(this.modelValue);
                            value.push(val);
                            this.$emit('update:modelValue', value);
                            this.$emit('changed', this.name, value);
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
            this.$emit('changed', this.name, value);
        },
    }
}
</script>
