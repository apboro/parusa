<template>
    <div class="pagination">
        <span class="pagination__shown">Показано {{ pagination.from }}-{{ pagination.to }} из {{
                pagination.total
            }}</span>
        <div class="pagination__per-page">
            <div>10</div>
            <span class="pagination__per-page">на страницу</span>
        </div>
        <div class="pagination__links">
            <span class="pagination__links-button pagination__links-button-icon"
                  :class="{'pagination__links-button-link' : pagination.current_page !== 1}"
                  @click="setPage(1, pagination.per_page)"><icon-backward-fast/></span>
            <span class="pagination__links-button pagination__links-button-icon"
                  :class="{'pagination__links-button-link' : pagination.current_page !== 1}"
                  @click="setPage(pagination.current_page - 1, pagination.per_page)"><icon-backward/></span>
            <span class="pagination__links-spacer"><span v-if="hasBefore()">...</span></span>
            <span class="pagination__links-button pagination__links-button-link" v-for="page in pages()"
                  :class="{'pagination__links-button-link-active': page === pagination.current_page}"
                  :key="page"
                  @click="setPage(page, pagination.per_page)"
            >{{ page }}</span>
            <span class="pagination__links-spacer"><span v-if="hasAfter()">...</span></span>
            <span class="pagination__links-button pagination__links-button-icon"
                  :class="{'pagination__links-button-link' : pagination.current_page !== pagination.last_page}"
                  @click="setPage(pagination.current_page + 1, pagination.per_page)"><icon-forward/></span>
            <span class="pagination__links-button pagination__links-button-icon"
                  :class="{'pagination__links-button-link' : pagination.current_page !== pagination.last_page}"
                  @click="setPage(pagination.last_page, pagination.per_page)"><icon-forward-fast/></span>
        </div>
    </div>

</template>

<script>
import IconBackward from "../Icons/IconBackward";
import IconBackwardFast from "../Icons/IconBackwardFast";
import IconForward from "../Icons/IconForward";
import IconForwardFast from "../Icons/IconForwardFast";

export default {
    emits: ['pagination'],

    props: {
        pagination: {
            type: Object,
            default: () => ({
                current_page: 1,
                last_page: 1,
                from: 1,
                to: 1,
                total: 1,
                per_page: 10,
            })
        },
    },

    components: {
        IconBackward,
        IconBackwardFast,
        IconForward,
        IconForwardFast,
    },

    data: () => ({
        max_links: 7,
    }),

    methods: {
        pages() {
            let pages = [];
            if (this.pagination.last_page <= this.max_links) {
                for (let i = 1; i <= this.pagination.last_page; i++) {
                    pages.push(i);
                }
            } else {
                let start = this.pagination.current_page - Math.floor(this.max_links / 2);
                if (start < 1) {
                    start = 1;
                }
                if (start + this.max_links > this.pagination.last_page) {
                    start = this.pagination.last_page - this.max_links + 1;
                }
                for (let i = start; i < start + this.max_links; i++) {
                    pages.push(i);
                }
            }
            return pages;
        },

        hasBefore() {
            return (this.pagination.last_page > this.max_links) && (this.pagination.current_page - Math.floor(this.max_links / 2) > 1);
        },

        hasAfter() {
            return (this.pagination.last_page > this.max_links) && (this.pagination.current_page - Math.floor(this.max_links / 2) + this.max_links - 1 < this.pagination.last_page);
        },

        setPage(page, perPage) {
            if (page < 1 || page > this.pagination.last_page || page === this.pagination.current_page) {
                return;
            }

            this.$emit('pagination', page, perPage);
        }
    }
}
</script>
