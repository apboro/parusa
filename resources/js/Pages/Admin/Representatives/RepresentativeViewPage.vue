<template>
    <LayoutPage :loading="processing" :title="data.data['full_name']"
                :breadcrumbs="[{caption: 'Представители', to: {name: 'representatives-list'}}]"
                :link="{name: 'representatives-list'}"
                :link-title="'К списку представителей'"
    >
        <template #actions>
            <GuiActionsMenu>
                <span class="link" @click="deleteRepresentative">Удалить представителя</span>
            </GuiActionsMenu>
        </template>

        <LayoutRoutedTabs :tabs="{personal: 'Персональные данные', access: 'Доступ'}" @change="tab = $event"/>

        <RepresentativeInfo v-if="tab === 'personal'" :representative-id="representativeId" :data="data.data" :editable="true" @update="update"/>
        <RepresentativeAccess v-if="tab === 'access'" :representative-id="representativeId" :data="data.data" :editable="true" @update="update"/>

    </LayoutPage>
</template>

<script>
import data from "@/Core/Data";
import DeleteEntry from "@/Mixins/DeleteEntry";
import LayoutPage from "@/Components/Layout/LayoutPage";
import GuiActionsMenu from "@/Components/GUI/GuiActionsMenu";
import LayoutRoutedTabs from "@/Components/Layout/LayoutRoutedTabs";
import RepresentativeInfo from "@/Pages/Admin/Representatives/Parts/RepresentativeInfo";
import RepresentativeAccess from "@/Pages/Admin/Representatives/Parts/RepresentativeAccess";


export default {
    components: {
        RepresentativeAccess,
        RepresentativeInfo,
        LayoutRoutedTabs,
        GuiActionsMenu,
        LayoutPage

    },

    mixins: [DeleteEntry],

    data: () => ({
        tab: null,
        data: data('/api/representatives/view'),
    }),

    computed: {
        representativeId() {
            return Number(this.$route.params.id);
        },

        processing() {
            return this.deleting || this.data.loading;
        },
    },

    created() {
        this.data.load({id: this.representativeId});
    },

    methods: {
        deleteRepresentative() {
            const name = this.data.data['full_name'];
            this.deleteEntry('Удалить представителя "' + name + '"?', '/api/representatives/delete', {id: this.representativeId})
                .then(() => {
                    this.$router.push({name: 'representatives-list'});
                });
        },

        update(payload) {
            Object.keys(payload).map(key => {
                this.data.data[key] = payload[key];
            })
        },
    }
}
</script>
