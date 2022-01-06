<template>
    <div class="base-files"
         :class="{'base-files__not-valid': !valid, 'base-files__differs': changed}"
    >
        <div class="base-files__container">
            <base-files-file v-for="(file, key) in modelValue"
                             :key="key"
                             :index="key"
                             :type="file.type"
                             :name="file.name"
                             :size="file.size"
                             @discard="discard"
            ></base-files-file>
            <label class="base-files__add" v-if="canAdd">
                <icon-plus :class="'base-files__add-icon'"/>
                <template v-if="canAddCount === 1">
                    <input class="base-files__add-input" type="file" accept="*" @change="handleFile">
                </template>
                <template v-else>
                    <input class="base-files__add-input" type="file" accept="*" multiple @change="handleFile">
                </template>
            </label>
        </div>
    </div>
</template>

<script>
import clone from "../../Helpers/Lib/clone";
import IconPlus from "../Icons/IconPlus";
import BaseFilesFile from "./Parts/BaseFilesFile";

export default {
    components: {
        BaseFilesFile,
        IconPlus,
    },
    props: {
        modelValue: {type: Array, default: () => ([])},
        name: String,
        original: {type: Array, default: () => ([])},

        required: {type: Boolean, default: false},
        disabled: {type: Boolean, default: false},
        valid: {type: Boolean, default: true},

        maxFiles: {type: Number, default: 0},
    },

    emits: ['update:modelValue', 'changed'],

    computed: {
        filesCount() {
            return this.modelValue.length;
        },
        canAdd() {
            return this.maxFiles === 0 || this.filesCount < this.maxFiles;
        },
        canAddCount() {
            return this.maxFiles === 0 ? 0 : this.maxFiles - this.filesCount;
        },
        changed() {
            return JSON.stringify(this.original) !== JSON.stringify(this.modelValue);
        },
        acceptable() {
            return [
                'text/plain',
                '.txt',
                'application/pdf',
                '.pdf',
                'application/msword',
                '.doc',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                '.docx',
                'application/vnd.oasis.opendocument.text',
                '.odt',
                'application/vnd.oasis.opendocument.spreadsheet',
                '.ods',
                'application/vnd.ms-excel',
                '.xls',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                '.xlsx',
            ];
        }
    },

    methods: {
        handleFile(e) {
            this.processFiles(e.target.files);
            e.target.value = '';
        },

        processFiles(files) {
            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                if (this.acceptable.indexOf(file.type) === -1) {
                    let extension = file.name.split('.');
                    extension = typeof extension[1] !== "undefined" ? '"' + extension[1] + '" ' : null;
                    this.$toast.error('Тип файла ' + extension + 'нельзя загрузить');
                    continue;
                }
                if(file.size > 10000000) {
                    console.log(file.size);
                    this.$toast.error('Файл "' + file.name + '" слишком большой. Можно загрузить файлы не больше 10Мб');
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
