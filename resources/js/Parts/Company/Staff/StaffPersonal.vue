<template>
    <div>
        <container w-50 mt-30>
            <value :title="'ФИО сотрудника'">{{ datasource.data['full_name'] }}</value>
            <value :title="'Должность'">{{ datasource.data['position'] }}</value>
            <value :title="'Дата заведения'">{{ datasource.data['created_at'] }}</value>
            <value :title="'Статус трудоустройства'">
                <span class="link" v-if="editable" @click="statusChange"><activity :active="datasource.data['active']"/>{{ datasource.data['status'] }}</span>
                <span v-else><activity :active="datasource.data.active"/>{{ datasource.data['status'] }}</span>
            </value>
        </container>

        <container w-50 mt-30 inline>
            <value :title="'Дата рождения'">{{ datasource.data['birth_date'] }}</value>
            <value :title="'Пол'">{{ datasource.data['gender'] }}</value>
            <value :title="'Email'">
                <a class="link" v-if="datasource.data.email" target="_blank" :href="'mailto:'+datasource.data.email">{{ datasource.data['email'] }}</a>
            </value>
            <value :title="'Рабочий телефон'">{{ datasource.data['work_phone'] }}
                <span v-if="datasource.data['work_phone_additional']"> доб. {{ datasource.data['work_phone_additional'] }}</span>
            </value>
            <value :title="'Мобильный телефон'">{{ datasource.data['mobile_phone'] }}</value>
        </container>

        <container w-50 mt-30 pl-40 inline>
            <value :class="'w-150px'" :title="'ВК'">{{ datasource.data['vkontakte'] }}</value>
            <value :class="'w-150px'" :title="'Facebook'">{{ datasource.data['facebook'] }}</value>
            <value :class="'w-150px'" :title="'Telegram'">{{ datasource.data['telegram'] }}</value>
            <value :class="'w-150px'" :title="'Skype'">{{ datasource.data['skype'] }}</value>
            <value :class="'w-150px'" :title="'WhatsApp'">{{ datasource.data['whatsapp'] }}</value>
        </container>

        <container w-100 mt-30>
            <value-area :title="'Заметки'" v-text="datasource.data['notes']"/>
        </container>

        <container mt-15 v-if="editable">
            <base-link-button :to="{ name: 'staff-edit', params: { id: staffId }}">Редактировать</base-link-button>
        </container>

        <pop-up ref="popup" v-if="editable"
                :title="'Изменить статус сотрудника'"
                :buttons="[{result: 'no', caption: 'Отмена', color: 'white'},{result: 'yes', caption: 'OK', color: 'green'}]"
                :manual="true"
        >
            <dictionary-drop-down :dictionary="'position_statuses'" v-model="current_status" :name="'status'" :original="initial_status"/>
        </pop-up>
    </div>
</template>

<script>
import Container from "../../../Components/GUI/Container";
import Value from "../../../Components/GUI/Value";
import Activity from "../../../Components/Activity";
import ValueArea from "../../../Components/GUI/ValueArea";
import BaseLinkButton from "../../../Components/Base/BaseLinkButton";
import PopUp from "../../../Components/PopUp";
import DictionaryDropDown from "../../../Components/Dictionary/DictionaryDropDown";

export default {
    props: {
        staffId: {type: Number, required: true},
        datasource: {type: Object},
        editable: {type: Boolean, default: false},
    },

    components: {
        Container,
        Value,
        Activity,
        ValueArea,
        BaseLinkButton,
        PopUp,
        DictionaryDropDown,
    },

    data: () => ({
        initial_status: null,
        current_status: null,
    }),

    methods: {
        statusChange() {
            this.initial_status = Number(this.datasource.data.status_id);
            this.current_status = this.initial_status;
            this.$refs.popup.show()
                .then(result => {
                    if (result === 'yes') {
                        this.$refs.popup.process(true);
                        axios.post('/api/company/staff/status', {id: this.staffId, status_id: this.current_status})
                            .then(response => {
                                this.$toast.success(response.data.message, 5000);
                                this.datasource.data.status = response.data.data.status;
                                this.datasource.data.status_id = response.data.data.status_id;
                                this.datasource.data.active = response.data.data.active;
                            })
                            .catch(error => {
                                this.$toast.error(error.response.data.message);
                            })
                            .finally(() => {
                                this.$refs.popup.hide();
                            })
                    } else {
                        this.$refs.popup.hide();
                    }
                });
        },
    }
}
</script>
