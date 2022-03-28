<template>
    <LayoutPage :loading="processing" :title="$route.meta['title']">
        <LayoutRoutedTabs v-if="data.is_loaded" :tabs="data.data" @change="current = $event"/>
        <Dictionary v-if="!processing" :dictionary="current"/>
    </LayoutPage>
</template>

<script>
import data from "@/Core/Data";
import LayoutPage from "@/Components/Layout/LayoutPage";
import LayoutRoutedTabs from "@/Components/Layout/LayoutRoutedTabs";
import Dictionary from "@/Pages/Admin/Dictionaries/Parts/Dictionary";

export default {
    components: {
        LayoutPage,
        LayoutRoutedTabs,
        Dictionary,
    },

    data: () => ({
        data: data('/api/dictionaries/index'),
        current: null,
    }),

    computed: {
        processing() {
            return (this.data.is_loading || this.current === null);
        },
    },

    created() {
        this.data.load();
    }
}
</script>
