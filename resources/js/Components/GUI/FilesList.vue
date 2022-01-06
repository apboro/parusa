<template>
    <div class="files-list">
        <template v-for="file in files">
            <a class="files-list__file" v-if="file['url']" :href="file['url']" :title="file['name']" target="_blank">
                <span class="files-list__file-icon">
                    <icon-file-type :type="file['type']"/>
                </span>
                <span class="files-list__file-name">{{ nameLimited(file['name']) }}</span>
            </a>
            <span class="files-list__file" v-else :title="file['name']">
                <span class="files-list__file-icon">
                    <icon-file-type :type="file['type']"/>
                </span>
                <span class="files-list__file-name">{{ nameLimited(file['name']) }}</span>
            </span>
        </template>
    </div>
</template>

<script>
import IconFileType from "../FileTypeIcons/IconFileType";

export default {
    components: {IconFileType},
    props: {
        files: {type: Array, default: () => ([])}
    },
    methods: {
        nameLimited(name) {
            if (name.length > 32) {
                return name.substring(0, 31) + '...';
            }

            return name;
        },
    }
}
</script>
