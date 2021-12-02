import { createStore } from 'vuex'

import DictionaryStore from "./dictionary-store";

export default createStore({
    modules: {
        dictionary: DictionaryStore,
    }
});
