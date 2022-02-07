<template>
    <LayoutPage :title="$route.meta['title']">
        <LayoutRoutedTabs :tabs="{general: 'Основные'}"
                          @change="tab = $event"
        />
    </LayoutPage>
</template>

<script>


import LayoutPage from "@/Components/Layout/LayoutPage";
import LayoutRoutedTabs from "@/Components/Layout/LayoutRoutedTabs";

export default {
    components: {
        LayoutRoutedTabs,
        LayoutPage,
    },

    data: () => ({
        tab: null,
    }),
}
</script>
