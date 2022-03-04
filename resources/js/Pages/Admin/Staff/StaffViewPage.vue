<template>
    <LayoutPage :loading="processing" :title="title"
                :breadcrumbs="[{caption: 'Сотрудники', to: {name: 'staff-list'}}]"
                :link="{name: 'staff-list'}"
                :link-title="'К списку сотрудников'"
    >
        <template v-slot:actions>
            <GuiActionsMenu>
                <span class="link" @click="deleteStaff">Удалить сотрудника</span>
            </GuiActionsMenu>
        </template>

        <LayoutRoutedTabs :tabs="{personal: 'Персональные данные', access: 'Доступ', roles: 'Роли'}" @change="tab = $event"/>

        <StaffInfo v-if="tab === 'personal'" :data="data.data" :staff-id="staffId" :editable="true" @update="update"/>
        <StaffAccess v-if="tab === 'access'" :data="data.data" :staff-id="staffId" :editable="true" @update="update"/>
        <StaffRoles v-if="tab === 'roles'" :data="data.data" :staff-id="staffId" :editable="true" @update="update"/>

    </LayoutPage>
</template>

<script>
import data from "@/Core/Data";
import DeleteEntry from "@/Mixins/DeleteEntry";
import LayoutPage from "@/Components/Layout/LayoutPage";
import GuiActionsMenu from "@/Components/GUI/GuiActionsMenu";
import LayoutRoutedTabs from "@/Components/Layout/LayoutRoutedTabs";
import StaffInfo from "@/Pages/Admin/Staff/Parts/StaffInfo";
import StaffAccess from "@/Pages/Admin/Staff/Parts/StaffAccess";
import StaffRoles from "@/Pages/Admin/Staff/Parts/StaffRoles";

export default {
    components: {
        StaffRoles,
        StaffAccess,
        StaffInfo,
        LayoutPage,
        GuiActionsMenu,
        LayoutRoutedTabs,
    },

    mixins: [DeleteEntry],

    data: () => ({
        data: data('/api/staff/view'),
        tab: null,
        trips_title: null,
    }),

    computed: {
        staffId() {
            return Number(this.$route.params.id);
        },
        processing() {
            return this.deleting || this.data.is_loading;
        },
        title() {
            return this.data.is_loaded ? this.data.data['full_name'] : '...';
        }
    },

    created() {
        this.data.load({id: this.staffId})
            .catch(response => response.code === 404 && this.$router.replace({name: '404'}));
    },

    methods: {
        deleteStaff() {
            const name = this.data.data['full_name'];
            this.deleteEntry('Удалить сотрудника "' + name + '"?', '/api/staff/delete', {id: this.staffId})
                .then(() => {
                    this.$router.push({name: 'staff-list'});
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
