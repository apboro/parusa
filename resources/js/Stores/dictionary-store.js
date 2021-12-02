const dictionariesUrl = '/api/dictionaries';

export default {
    namespaced: true,

    state: () => ({
        timestamps: {},
        dictionaries: {},
        states: {},
    }),

    mutations: {
        setDictionary(state, payload) {
            state.dictionaries[payload.name] = payload.data;
        },
        setDictionaryState(state, payload) {
            state.states[payload.name] = payload.data;
        },
        setDictionaryTimestamp(state, payload) {
            state.timestamps[payload.name] = payload.data;
        },
    },

    getters: {
        dictionary: (state) => (dictionary) => {
            return typeof state.dictionaries[dictionary] !== "undefined" ? state.dictionaries[dictionary] : null;
        },
        ready: (state) => (dictionary) => {
            return typeof state.states[dictionary] !== "undefined" ? state.states[dictionary] : null;
        },
    },

    actions: {
        async refresh({commit, state}, dictionary) {

            commit('setDictionaryState', {name: dictionary, data: false});

            return new Promise(function (resolve, reject) {
                let headers = {};
                if (typeof state.timestamps[dictionary] !== "undefined" && state.timestamps[dictionary] !== null) {
                    headers['if-modified-since'] = state.timestamps[dictionary];
                }
                axios.post(dictionariesUrl, {dictionary: dictionary}, {
                    headers: headers,
                })
                    .then(response => {
                        // set loading state
                        commit('setDictionaryState', {name: dictionary, data: true});
                        commit('setDictionary', {name: dictionary, data: response.data.list});
                        commit('setDictionaryTimestamp', {
                            name: dictionary,
                            data: typeof response.headers['last-modified'] !== "undefined" ? response.headers['last-modified'] : null
                        });
                        resolve();
                    })
                    .catch(error => {
                        if (error.response.status === 304) {
                            commit('setDictionaryState', {name: dictionary, data: true});
                            resolve();
                        }
                        reject();
                    });
            });
        },
    },
};
