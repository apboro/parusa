<template>
    <Order :returnable="false" :printable="true"/>
</template>

<script>
import Order from "@/Pages/Terminal/Parts/Order";

export default {
    components: {
        Order,
    }
}
</script>
