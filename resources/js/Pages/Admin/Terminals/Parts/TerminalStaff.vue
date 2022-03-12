<template>
    <div>
        <div class="text-right mb-20" v-if="editable">
            <GuiButton @click="addTerminalUser">Прикрепить кассира</GuiButton>
        </div>

        <ListTable v-if="data['staff'] && data['staff'].length > 0" :titles="['Фио','Должность','Контакты','Доступ в систему']" :has-action="true">
            <ListTableRow v-for="(user, key) in data['staff']" :key="key">
                <ListTableCell>
                    <GuiActivityIndicator :active="user['active']"/>
                    <router-link class="link" :to="{ name: 'staff-view', params: { id: user['id'] }}">{{ user['name'] }}</router-link>
                </ListTableCell>
                <ListTableCell>
                    {{ user['position'] }}
                </ListTableCell>
                <ListTableCell>
                    <div v-if="user['email']">
                        <a class="link" target="_blank" :href="'mailto:' + user['email']">{{ user['email'] }}</a>
                    </div>
                    <div v-if="user['mobile_phone']">
                        <span>{{ user['work_phone'] }}</span>
                        <span v-if="user['work_phone_add']"> доб. {{ user['work_phone_add'] }}</span>
                    </div>
                    <div v-if="user['mobile_phone']">
                        {{ user['mobile_phone'] }}
                    </div>
                </ListTableCell>
                <ListTableCell>
                    <GuiAccessIndicator :locked="!user['has_access']"/>
                    <span>{{ user['has_access'] ? 'открыт' : 'закрыт' }}</span>
                </ListTableCell>
                <ListTableCell>
                    <GuiActionsMenu :title="null">
                        <span class="link" @click="detach(user)">Открепить кассира</span>
                    </GuiActionsMenu>
                </ListTableCell>
            </ListTableRow>
        </ListTable>

        <GuiMessage border v-else>Нет привязанных кассиров</GuiMessage>

        <FormPopUp :title="'Прикрепление кассира'"
                   :form="form"
                   :options="{id: terminalId}"
                   ref="popup"
        >
            <GuiContainer w-350px>
                <FormDropdown :form="form" :name="'staff_id'"
                              :options="staff"
                              :identifier="'id'"
                              :show="'name'"
                              :placeholder="'Выберите сотрудника'"
                              :fresh="true"
                              :hide-title="true"
                              :search="true"
                              @drop="updateDictionary"
                />
            </GuiContainer>
        </FormPopUp>
    </div>
</template>

<script>

import GuiButton from "@/Components/GUI/GuiButton";
import FormPopUp from "@/Components/FormPopUp";
import form from "@/Core/Form";
import GuiContainer from "@/Components/GUI/GuiContainer";
import FormDictionary from "@/Components/Form/FormDictionary";
import ListTable from "@/Components/ListTable/ListTable";
import ListTableRow from "@/Components/ListTable/ListTableRow";
import ListTableCell from "@/Components/ListTable/ListTableCell";
import GuiActivityIndicator from "@/Components/GUI/GuiActivityIndicator";
import GuiAccessIndicator from "@/Components/GUI/GuiAccessIndicator";
import GuiMessage from "@/Components/GUI/GuiMessage";
import GuiActionsMenu from "@/Components/GUI/GuiActionsMenu";
import DeleteEntry from "@/Mixins/DeleteEntry";
import FormDropdown from "@/Components/Form/FormDropdown";

export default {
    props: {
        terminalId: {type: Number, required: true},
        data: {type: Object},
        editable: {type: Boolean, default: false},
    },

    emits: ['attach', 'detach'],

    mixins: [DeleteEntry],

    components: {
        FormDropdown,
        GuiActionsMenu,
        GuiMessage,
        GuiAccessIndicator,
        GuiActivityIndicator,
        ListTableCell,
        ListTableRow,
        ListTable,
        FormDictionary,
        GuiContainer,
        FormPopUp,
        GuiButton
    },

    computed: {
        assigned() {
            if (this.data === null) {
                return [];
            }
            let assigned = [];
            this.data['staff'].map(staff => {
                assigned.push(staff['id']);
            });
            return assigned;
        },
        staff() {
            if (this.$store.getters['dictionary/ready']('terminal_positions') === null) {
                return [];
            }
            return this.$store.getters['dictionary/dictionary']('terminal_positions').filter(position => this.assigned.indexOf(position['id']) === -1);
        },
    },

    data: () => ({
        form: form(null, '/api/terminals/attach'),
    }),

    created() {
        this.form.toaster = this.$toast;
        this.updateDictionary();
    },

    methods: {
        addTerminalUser() {
            this.form.reset();
            this.form.set('staff_id', null, 'required', 'Сотрудник', true);
            this.form.load();
            this.$refs.popup.show()
                .then(response => {
                    this.$emit('attach', response['payload']);
                })
        },
        detach(user) {
            this.deleteEntry(`Открепить кассира ${user['name']}?`, '/api/terminals/detach', {id: this.terminalId, data: {staff_id: user['id']}}, 'Открепить')
                .then(() => {
                    this.$emit('detach', user['id']);
                });
        },
        updateDictionary() {
            this.$store.dispatch('dictionary/refresh', 'terminal_positions');
        },
    }
}

</script>

