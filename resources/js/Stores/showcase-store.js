export default {
    namespaced: true,

    mutations: {
        trip_id: (state, payload) => state.trip_id = payload,
        order: (state, payload) => state.order = payload,
        order_id: (state, payload) => state.order_id = payload,
        cart: (state, payload) => state.cart = payload,
    },

    getters: {
        trip_id(state) {
            return typeof state.trip_id !== "undefined" ? state.trip_id : null;
        },
        order(state) {
            return typeof state.order !== "undefined" ? state.order : null;
        },
        order_id(state) {
            return typeof state.order_id !== "undefined" ? state.order_id : null;
        },
        cart(state) {
            return typeof state.cart !== "undefined" ? state.cart : null;
        },
    }
};
