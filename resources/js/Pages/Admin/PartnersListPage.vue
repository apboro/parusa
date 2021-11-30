<template>
    <page>
        <template v-slot:header>Компании-партнеры</template>
        <!-- list here -->
        <base-pagination :pagination="pagination" @pagination="setPagination"/>
    </page>
</template>

<script>
import Page from "../../Layouts/Page";
import BasePagination from "../../Components/Base/BasePagination";

export default {
    components: {
        Page,
        BasePagination,
    },

    data: () => ({
        pagination: {
            current_page: 5,
            last_page: 10,
            from: 51,
            to: 60,
            total: 100,
            per_page: 10,
        },
    }),

    methods: {
        setPagination(page, perPage) {
            console.log(page, perPage);
            this.pagination.current_page = page;
            this.pagination.from = (page - 1) * 10 + 1;
            this.pagination.to = (page - 1) * 10 + 1 + 10;
        }
    },
}
</script>
