<template>
    <LayoutPage :loading="processing" :title="title"
                :breadcrumbs="[{caption: 'Мобильные кассы', to: {name: 'terminals-list'}}]"
                :link="{name: 'terminals-list'}"
                :link-title="'К списку мобильных касс'"
    >
        <template v-slot:actions>
            <GuiActionsMenu>
                <span class="link" @click="deleteTerminal">Удалить мобильную кассу</span>
            </GuiActionsMenu>
        </template>

        <LayoutRoutedTabs :tabs="{description: 'Описание кассы', staff: 'Кассиры', sales: 'Операции'}" @change="tab = $event"/>

        <TerminalInfo v-if="tab === 'description'" :terminal-id="terminalId" :data="data.data" :editable="true" @update="update"/>
        <TerminalStaff v-if="tab === 'staff'" :terminal-id="terminalId" :data="data.data" :editable="true" @attach="attach" @detach="detach"/>
        <!-- SALES -->

    </LayoutPage>
</template>

<script>
import data from "@/Core/Data";
import DeleteEntry from "@/Mixins/DeleteEntry";
import LayoutPage from "@/Components/Layout/LayoutPage";
import GuiActionsMenu from "@/Components/GUI/GuiActionsMenu";
import LayoutRoutedTabs from "@/Components/Layout/LayoutRoutedTabs";
import TerminalInfo from "@/Pages/Admin/Terminals/Parts/TerminalInfo";
import TerminalStaff from "@/Pages/Admin/Terminals/Parts/TerminalStaff";

export default {
    components: {
        TerminalStaff,
        TerminalInfo,
        LayoutRoutedTabs,
        GuiActionsMenu,
        LayoutPage,
    },

    mixins: [DeleteEntry],

    data: () => ({
        tab: null,
        data: data('/api/terminals/view'),
    }),

    computed: {
        terminalId() {
            return Number(this.$route.params.id);
        },
        processing() {
            return false;
        },
        title() {
            return `Касса №${this.terminalId}`;
        }
    },

    created() {
        this.data.load({id: this.terminalId})
            .catch(response => response.code === 404 && this.$router.replace({name: '404'}));
    },

    methods: {
        deleteTerminal() {
            this.deleteEntry('Удалить кассу №' + this.terminalId + '?', '/api/terminals/delete', {id: this.terminalId})
                .then(() => {
                    this.$router.push({name: 'terminals-list'});
                });
        },
        update(payload) {
            Object.keys(payload).map(key => {
                this.data.data[key] = payload[key];
            });
        },
        attach(user) {
            this.data.data['staff'].push(user);
        },
        detach(user_id) {
            this.data.data['staff'] = this.data.data['staff'].filter(user => user['id'] !== user_id);
        },
    }
}
</script>
