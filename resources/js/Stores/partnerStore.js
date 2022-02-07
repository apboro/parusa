import { createStore } from 'vuex'

import DictionaryStore from "./dictionary-store";
import PartnerStore from "./partner-store";

export default createStore({
    modules: {
        dictionary: DictionaryStore,
        partner: PartnerStore,
    }
});
