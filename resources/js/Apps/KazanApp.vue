<template>
    <div v-if="excursions && excursions.length > 0" style="width: 70%">
        <div style="display: flex; flex-direction: row">
            <div v-for="excursion in excursions" style="background: #aee6b7; margin-right: 10px">
                <div>
                    <div style="cursor: pointer; text-decoration: underline" @click="handleExcursionClick(excursion)">
                        {{ excursion.name }}
                    </div>
                    <img width="200" :src="excursion.excursion_first_image_url"/>
                </div>
            </div>
        </div>
        <div v-if="selectedExcursion">
            <ShowcaseForCities :crm_url="crm_url" :excursion="selectedExcursion" :debug="debug"/>
        </div>
    </div>


</template>

<script setup>
import {defineProps, onBeforeMount, ref, computed} from 'vue';
import ShowcaseApp3 from '@/Apps/ShowcaseApp3.vue';
import ShowcaseForCities from "@/Apps/ShowcaseForCities.vue";

const props = defineProps({
    crm_url: {type: String, default: 'https://lk.excurr.ru'},
    debug: {type: Boolean, default: false},
});

onBeforeMount(() => {
    axios.get('/city_showcase_data').then((response) => {
        info.value = response.data.data;
    })
});

const info = ref(null);
const selectedExcursion = ref(null);
const excursions = computed(() => info.value?.excursions);
const excursion_programs = computed(() => info.value?.excursion_programs);
const piers = computed(() => info.value?.piers);

function handleExcursionClick(excursion) {
    selectedExcursion.value = excursion;
}
</script>
