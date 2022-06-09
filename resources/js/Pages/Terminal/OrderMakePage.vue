<template>
    <LayoutPage :title="title" :loading="processing">

        <OrderMakeProcess v-if="data.is_loaded && data.data['has_order']" :data="data.data" @update="data.load()" :external-processing="processing"/>

        <OrderMakeCart v-else-if="data.is_loaded" :data="data.data" @update="data.load()"/>

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
        title() {
            if (!this.data.is_loaded) return 'Оформление заказа';
            if (this.data.data['status']['creating']) return 'Оформление заказа';
            if (this.data.data['status']['created']) return 'Оплата заказа';
            if (this.data.data['status']['waiting_for_payment']) return 'Приём оплаты через терминал';
            if (this.data.data['status']['finishing']) return 'Печать билетов';
        },
    },

    created() {
        this.data.load();
    },

    methods: {},
}
</script>

