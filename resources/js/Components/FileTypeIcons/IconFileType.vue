<template>
    <component :is="icon"/>
</template>

<script>
import IconFileTypePdf from "./IconFileTypePdf";
import IconFileTypeDoc from "./IconFileTypeDoc";
import IconFileTypeXls from "./IconFileTypeXls";
import IconFileTypeDefault from "./IconFileTypeDefault";
import IconFileTypeJpg from "@/Components/FileTypeIcons/IconFileTypeJpg";
import IconFileTypePng from "@/Components/FileTypeIcons/IconFileTypePng";

export default {
    props: {
        type: String,
    },
    computed: {
        icon() {
            switch (this.type) {
                case 'image/jpg':
                    return IconFileTypeJpg;
                case 'image/png':
                    return IconFileTypePng;
                case 'application/pdf':
                    return IconFileTypePdf;
                case 'application/msword':
                case  'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
                case 'application/vnd.oasis.opendocument.text':
                    return IconFileTypeDoc;
                case 'application/vnd.oasis.opendocument.spreadsheet':
                case  'application/vnd.ms-excel':
                case 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet':
                    return IconFileTypeXls;
            }

            return IconFileTypeDefault;
        }
    }
}
</script>
