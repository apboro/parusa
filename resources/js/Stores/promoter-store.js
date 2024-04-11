const dataUrl = '/api/promoters/promoter/info';

export default {
    namespaced: true,

    state: () => ({
        loaded: false,
        total: 0,
        order_amount: 0,
        tariff: 0,
        can_send_sms: false,
        self_pay: false
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
        setSMS(state, value) {
            state.can_send_sms = value;
        },
        setSelfPay(state, value) {
            state.self_pay = value;
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
                        commit('setSMS', response.data.data.can_send_sms);
                        commit('setSelfPay', response.data.data.self_pay);
                        resolve();
                    })
                    .catch(error => {
                        reject(error);
                    });
            });
        },
    },
};
