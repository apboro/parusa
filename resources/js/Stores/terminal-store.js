const dataUrl = '/api/terminals/terminal/info';

export default {
    namespaced: true,

    state: () => ({
        loaded: false,

        order_amount: 0,
        current: null,
    }),

    mutations: {
        setLoaded(state, value) {
            state.loaded = value;
        },
        setOrderAmount(state, value) {
            state.order_amount = value;
        },
        setCurrent(state, value) {
            state.current = value;
        },
    },

    actions: {
        async refresh({commit}) {

            return new Promise(function (resolve, reject) {
                axios.post(dataUrl, {})
                    .then(response => {
                        commit('setLoaded', true);
                        commit('setOrderAmount', response.data.data['order_amount']);
                        commit('setCurrent', response.data.data['current']);
                        resolve();
                    })
                    .catch(error => {
                        reject(error);
                    });
            });
        },
    },
};
