const dataUrl = '/api/partners/partner/info';

export default {
    namespaced: true,

    state: () => ({
        loaded: false,

        amount: 0,
        limit: 0,
        total: 0,
        partner_type: 0,
        reserves: 0,
        can_reserve: false,
        order_amount: 0,
        new_news: 0,
    }),

    mutations: {
        setLoaded(state, value) {
            state.loaded = value;
        },
        setAmount(state, value) {
            state.amount = value;
        },
        setLimit(state, value) {
            state.limit = value;
        },
        setTotal(state, value) {
            state.total = value;
        },
        setReserves(state, value) {
            state.reserves = value;
        },
        setCanReserve(state, value) {
            state.can_reserve = value;
        },
        setOrderAmount(state, value) {
            state.order_amount = value;
        },
        setPartnerType(state, value) {
            state.partner_type = value;
        },
        setNewNews(state, value) {
            state.new_news = value;
        },
    },

    actions: {
        async refresh({commit}) {

            return new Promise(function (resolve, reject) {
                axios.post(dataUrl, {})
                    .then(response => {
                        commit('setLoaded', true);
                        commit('setAmount', response.data.data.amount);
                        commit('setLimit', response.data.data.limit);
                        commit('setCanReserve', Boolean(response.data.data.can_reserve));
                        commit('setReserves', response.data.data.reserves);
                        commit('setOrderAmount', response.data.data.order_amount);
                        commit('setTotal', response.data.data.total);
                        commit('setPartnerType', response.data.data.partner_type);
                        commit('setNewNews', response.data.data.new_news)
                        resolve();
                    })
                    .catch(error => {
                        reject(error);
                    });
            });
        },
    },
};
