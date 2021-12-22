<template>
    <div>
        <container w-50 mt-30 inline>
            <value :title="'ФИО представителя'">{{ datasource.data['full_name'] }}</value>
            <value :title="'Дата заведения'">{{ datasource.data['created_at'] }}</value>
            <value :title="'Пол'">{{ datasource.data['gender'] }}</value>
            <value :title="'Дата рождения'">{{ datasource.data['birth_date'] }}</value>
            <value :title="'Email'"><a class="link" v-if="datasource.data.email" target="_blank"
                                       :href="'mailto:'+datasource.data.email"
            >{{ datasource.data['email'] }}</a></value>
            <value :title="'Мобильный телефон'">{{ datasource.data['mobile_phone'] }}</value>
        </container>

        <container w-50 mt-30 pl-40 inline>
            <value :class="'w-150px'" :title="'ВК'">{{ datasource.data['vkontakte'] }}</value>
            <value :class="'w-150px'" :title="'Facebook'">{{ datasource.data['facebook'] }}</value>
            <value :class="'w-150px'" :title="'Telegram'">{{ datasource.data['telegram'] }}</value>
            <value :class="'w-150px'" :title="'Skype'">{{ datasource.data['skype'] }}</value>
            <value :class="'w-150px'" :title="'WhatsApp'">{{ datasource.data['whatsapp'] }}</value>
        </container>

        <container w-100 mt-50>
            <heading mb-20>Связан с партнерами</heading>
            <base-table>
                <template v-slot:header>
                    <base-table-head :header="['Партнер', 'Должность', 'Рабочий телефон, email', 'Статус доступа']"
                                     :has-actions="editable"/>
                </template>
                <base-table-row v-for="(position, key) in datasource.data.positions" v-if="datasource.data.positions"
                                :key="key">
                    <base-table-cell>
                        <router-link :class="'link'" :to="{name: 'partners-view', props: {id: position['partner_id']}}">
                            {{ position['partner'] }}
                        </router-link>
                    </base-table-cell>
                    <base-table-cell>{{ position['title'] }}</base-table-cell>
                    <base-table-cell>
                        <base-table-cell-item>
                            <a class="link" :href="'mailto:' + position['email']" target="_blank">{{
                                    position['email']
                                }}</a>
                        </base-table-cell-item>
                        <base-table-cell-item>
                            {{ position['work_phone'] }}
                            <span v-if="position['work_phone_additional']">доб. {{
                                    position['work_phone_additional']
                                }}</span>
                        </base-table-cell-item>
                    </base-table-cell>
                    <base-table-cell>
                        <span class="link" v-if="editable" @click=""><activity
                            :active="position['active']"/>{{ position['status'] }}</span>
                        <span v-else><activity :active="position['active']"/>{{ position['status'] }}</span>
                    </base-table-cell>
                    <base-table-cell v-if="editable">
                        <actions-menu :title="null">
                            <span class="link">Редактировать</span>
                            <span class="link">Открепить</span>
                        </actions-menu>
                    </base-table-cell>
                </base-table-row>
            </base-table>
            <message v-if="!datasource.data.positions || datasource.data.positions.length === 0">Не связан ни с одним
                партнёром
            </message>
            <div class="text-right mt-20" v-if="editable">
                <span class="link">Прикрепить к компании-партнеру</span>
            </div>
        </container>

        <container w-100 mt-50>
            <value-area :title="'Заметки'" v-text="datasource.data['notes']"/>
        </container>

        <container mt-15 v-if="editable">
            <base-link-button :to="{ name: 'representatives-edit', params: { id: representativeId }}">Редактировать
            </base-link-button>
        </container>
    </div>
</template>

<script>
import Container from "../../../Components/GUI/Container";
import Value from "../../../Components/GUI/Value";
import ValueArea from "../../../Components/GUI/ValueArea";
import BaseLinkButton from "../../../Components/Base/BaseLinkButton";
import UseBaseTableBundle from "../../../Mixins/UseBaseTableBundle";
import Activity from "../../../Components/Activity";
import ActionsMenu from "../../../Components/ActionsMenu";
import Heading from "../../../Components/GUI/Heading";
import Message from "../../../Layouts/Parts/Message";

export default {
    props: {
        representativeId: {type: Number, required: true},
        datasource: {type: Object},
        editable: {type: Boolean, default: false},
    },

    components: {
        Message,
        Heading,
        ActionsMenu,
        Activity,
        ValueArea,
        Value,
        Container,
        BaseLinkButton,
    },
    mixins: [UseBaseTableBundle],

    data: () => ({
        initial_status: null,
        current_status: null,
    }),

    methods: {}
}
</script>
