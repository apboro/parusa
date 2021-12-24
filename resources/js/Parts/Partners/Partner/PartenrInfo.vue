<template>
    <div>
        <container w-50 mt-30>
            <value :title="'Название партнера'">{{ datasource.data['name'] }}</value>
            <value :title="'Дата заведения'">{{ datasource.data['created_at'] }}</value>
            <value :title="'Тип партнера'">{{ datasource.data['type'] }}</value>
            <value :title="'Статус'">
                <span class="link" v-if="editable" @click="statusChange"><activity :active="datasource.data.active"/>{{ datasource.data.status }}</span>
                <span v-else><activity :active="datasource.data.active"/>{{ datasource.data.status }}</span>
            </value>
        </container>
        <container w-50 mt-30>
            <value :title="'Билеты для гидов'">
                <span class="link" v-if="editable" @click="statusChange">{{ datasource.data['tickets_for_guides'] }}</span>
                <span v-else>{{ datasource.data['tickets_for_guides'] }}</span>
            </value>
            <hint mt-5 mb-10>При значении "0" партнер не может включать в заказ бесплатные билеты для гидов. Любое положительное число разрешает данную возможность и определяет
                максимальное количество таких билетов для одного заказа. Например, при значении "1" к заказу можно будет добавить 1 билет для гида.
            </hint>
            <value :title="'Бронирование билетов'">
                <span class="link" v-if="editable" @click="statusChange">{{ datasource.data['tickets_for_guides'] === 1 ? 'Разрешено' : 'Запрещено' }}</span>
                <span v-else>{{ datasource.data['tickets_for_guides'] }}</span>
            </value>
        </container>

        <container w-100 mt-50>
            <value-area :title="'документы'"/>
        </container>

        <container w-100 mt-50>
            <value-area :title="'Заметки'" v-text="datasource.data['notes']"/>
        </container>

        <container mt-15 v-if="editable">
            <base-link-button :to="{ name: 'partners-edit', params: { id: partnerId }}">Редактировать</base-link-button>
        </container>

        <pop-up ref="popup" v-if="editable"
                :manual="true"
                :title="'Изменить статус партнёра'"
                :buttons="[
                    {result: 'no', caption: 'Отмена', color: 'white'},
                    {result: 'yes', caption: 'OK', color: 'green'}
                ]"
        >
            <dictionary-drop-down
                :dictionary="'partner_statuses'"
                :original="initial_status"
                v-model="current_status"
                :name="'status'"
            />
        </pop-up>
    </div>
</template>

<script>
import UseBaseTableBundle from "../../../Mixins/UseBaseTableBundle";
import Container from "../../../Components/GUI/Container";
import TextContainer from "../../../Layouts/Parts/TextContainer";
import BaseLinkButton from "../../../Components/Base/BaseLinkButton";
import PopUp from "../../../Components/PopUp";
import DictionaryDropDown from "../../../Components/Dictionary/DictionaryDropDown";
import Value from "../../../Components/GUI/Value";
import ValueArea from "../../../Components/GUI/ValueArea";
import Activity from "../../../Components/Activity";
import Message from "../../../Layouts/Parts/Message";
import Hint from "../../../Components/GUI/Hint";

export default {
    mixins: [UseBaseTableBundle],

    props: {
        partnerId: {type: Number, required: true},
        datasource: {type: Object},
        editable: {type: Boolean, default: false},
    },

    components: {
        Hint,
        Message,
        Activity,
        ValueArea,
        Value,
        DictionaryDropDown,
        TextContainer,
        Container,
        BaseLinkButton,
        PopUp,
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
                        axios.post('/api/partners/status', {id: this.partnerId, status_id: this.current_status})
                            .then(response => {
                                this.$toast.success(response.data.data.message, 2000);
                                this.datasource.data.status = response.data.data.status;
                                this.datasource.data.status_id = response.data.data.status_id;
                                this.datasource.data.active = response.data.data.active;
                            })
                            .catch(error => {
                                this.$toast.error(error.response.data.status);
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
