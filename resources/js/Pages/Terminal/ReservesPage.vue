<template>
    <LayoutPage :title="$route.meta['title']" :loading="processing">

    </LayoutPage>
</template>

<script>
import LayoutPage from "@/Components/Layout/LayoutPage";

export default {
    components: {
        LayoutPage,
    },

    computed: {
        processing() {
            return false;
        }
    }
}
</script>
