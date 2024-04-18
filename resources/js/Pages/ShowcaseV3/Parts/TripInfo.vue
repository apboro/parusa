<script>
export default {
    name: "TripInfo",
    props:{
        trip: Object
    }
}
</script>

<template>
    <div style="display: flex; flex-direction: column">
        <p> Информация об экскурсии: {{ trip['excursion'] }}</p>
        <p> Время в пути: <span class="ap-not-brake">{{ trip['duration'] }} мин.</span></p>
        <p>Осталось билетов {{ trip['tickets_left'] }}</p>
    </div>

</template>

<style scoped lang="scss">

</style>
