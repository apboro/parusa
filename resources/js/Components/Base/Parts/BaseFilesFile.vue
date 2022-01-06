<template>
    <div class="base-files__file">
        <span class="base-files__file-discard" @click="$emit('discard', index)"></span>
        <div class="base-files__file-icon">
            <icon-file-type :type="type"/>
        </div>
        <span class="base-files__file-name" :title="name">{{ nameLimited }}</span>
        <div class="base-files__file-size">{{ displaySize }}</div>
    </div>
</template>

<script>
import IconFileType from "../../FileTypeIcons/IconFileType";

export default {
    components: {
        IconFileType,
    },

    props: {
        index: Number,
        type: String,
        name: String,
        size: Number,
    },

    emits: ['discard'],

    computed: {
        displaySize() {
            if (this.size > 1000000) {
                return Math.round(this.size / 1000 / 10) / 100 + ' Мб';
            }
            return Math.round(this.size / 10) / 100 + ' Кб';
        },
        nameLimited() {
            if (this.name.length > 38) {
                return this.name.substring(0, 37) + '...';
            }

            return this.name;
        },
    }
}
</script>
