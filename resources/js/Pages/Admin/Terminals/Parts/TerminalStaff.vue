<template>
    <div>

    </div>
</template>

<script>
import LoadingProgress from "@/Components/LoadingProgress";

export default {
    props: {
        terminalId: {type: Number, required: true},
        editable: {type: Boolean, default: false},
    },

    components: {
        LoadingProgress,
    },

    data: () => ({

    }),
}

</script>

