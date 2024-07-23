<template>
    <div v-if="excursions.length > 0">
        <div v-for="excursion in excursions">
            {{ excursion.name }}
        </div>
    </div>

    <div style="width: 70%; display: flex; justify-content: center; align-items: center">
        <ShowcaseApp3 v-if="selectedExcursion" :crm_url="crm_url"/>
    </div>
</template>

<script setup>
import {defineProps, onBeforeMount, ref, computed} from 'vue';
import ShowcaseApp3 from '@/Apps/ShowcaseApp3.vue';
import {useStore} from 'vuex';

const props = defineProps({
    crm_url: {type: String, default: 'https://lk.excurr.ru'},
    debug: {type: Boolean, default: false},
});

const store = useStore();
onBeforeMount(() => {
    axios.get('/city_showcase_data').then((response) =>{

    })
});

const selectedExcursion = ref(1);
const excursions = computed(() => store.state.dictionary?.dictionaries?.excursions || []);
const excursion_programs = computed(() => store.state.dictionary?.dictionaries?.excursion_programs || []);
const piers = computed(() => store.state.dictionary?.dictionaries?.piers || []);

</script>
