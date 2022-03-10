<template>
    <LayoutPage :title="$route.meta['title']" :loading="processing">

        <OrderMakeProcess v-if="data.is_loaded && data.data['has_order']" :data="data.data" @update="data.load()"/>

        <OrderMakeCart v-else-if="data.is_loaded" :data="data.data" @order="data.load()"/>

    </LayoutPage>
</template>

<script>
import data from "@/Core/Data";
import LayoutPage from "@/Components/Layout/LayoutPage";
import OrderMakeProcess from "@/Pages/Terminal/Parts/OrderMakeProcess";
import OrderMakeCart from "@/Pages/Terminal/Parts/OrderMakeCart";

export default {
    components: {
        OrderMakeCart,
        OrderMakeProcess,
        LayoutPage,
    },

    data: () => ({
        data: data('/api/cart/terminal'),
    }),

    computed: {
        processing() {
            return Boolean(this.data.is_loading);
        },
    },

    created() {
        this.data.load();
    },

    methods: {},
}
</script>

