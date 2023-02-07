const initialRoles = typeof window.roles !== "undefined" ? JSON.parse(window.roles) : [];

export default {
    namespaced: true,

    state: () => ({
        roles: initialRoles,
    }),

    mutations: {
    },

    getters: {
        roles(state) {
            return state.roles;
        },
        hasRole: (state) => (role) => {
            return state.roles.indexOf(role) !== -1;
        },
    },

};
