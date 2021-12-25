<template>
    <loading-progress :loading="processing">
        <container w-100 v-if="!processing">
            <p v-for="(item, key) in data.data" :key="key">{{ item['id'] }} {{ item['name'] }}</p>
        </container>
    </loading-progress>
</template>

<script>
import genericDataSource from "../../Helpers/Core/genericDataSource";
import Container from "../../Components/GUI/Container";
import LoadingProgress from "../../Components/LoadingProgress";

export default {
    components: {LoadingProgress, Container},
    props: {
        dictionary: {type: String, required: true},
    },

    data: () => ({
        data: null,
    }),

    watch: {
        dictionary(value) {
            this.init(value);
        },
    },

    computed: {
        processing() {
            return this.data === null || !!this.data.loading;
        },
    },

    created() {
        this.data = genericDataSource('/api/dictionaries/details');
    },

    mounted() {
        this.init(this.dictionary);
    },

    methods: {
        init(dictionary) {
            this.data.load({name: dictionary});
        },
    }
}
</script>
