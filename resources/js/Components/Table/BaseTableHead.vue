<template>
    <thead class="base-table__header" v-if="headers">
    <tr class="base-table__header-row">
        <td class="base-table__header-cell" v-for="(cell, key) in headers" :key="key">{{ cell }}</td>
    </tr>
    </thead>
</template>

<script>
import empty from "../../Helpers/Lib/empty";

export default {
    props: {
        header: {
            type: [Array, Object],
            default: null,
        }
    },

    computed: {
        headers() {
            return !empty(this.header) ? this.header : null;
        }
    }
}
</script>
