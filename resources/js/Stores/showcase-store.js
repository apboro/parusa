export default {
    namespaced: true,

    mutations: {
        trip_id: (state, payload) => state.trip_id = payload,
        cart: (state, payload) => state.cart = payload,
    },

    getters: {
        trip_id(state) {
            return state.trip_id;
        },
        cart(state) {
            return state.cart;
        },
    }
};
