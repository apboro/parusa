const dataUrl = '/api/promoters/promoter/info';

export default {
    namespaced: true,

    state: () => ({
        loaded: false,
        total: 0,
        order_amount: 0,
        tariff: 0,
    }),

    mutations: {
        setLoaded(state, value) {
            state.loaded = value;
        },
        setTotal(state, value) {
            state.total = value;
        },
        setOrderAmount(state, value) {
            state.order_amount = value;
        },
        setTariff(state, value) {
            state.tariff = value;
        },
    },

    actions: {
        async refresh({commit}) {

            return new Promise(function (resolve, reject) {
                axios.post(dataUrl, {})
                    .then(response => {
                        commit('setLoaded', true);
                        commit('setOrderAmount', response.data.data.order_amount);
                        commit('setTotal', response.data.data.total);
                        commit('setTariff', response.data.data.tariff);
                        resolve();
                    })
                    .catch(error => {
                        reject(error);
                    });
            });
        },
    },
};
