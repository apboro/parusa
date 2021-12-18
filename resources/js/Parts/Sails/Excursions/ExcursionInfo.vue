<template>
    <div>
        <container w-50 mt-30 inline>
            <value :title="'Название'">{{ datasource.data.name }}</value>
            <value :title="'Тип программы'">{{ datasource.data.programs ? datasource.data.programs.join(', ') : '' }}</value>
            <value :title="'Продолжительность'">{{ datasource.data.duration }} минут</value>
            <value :title="'Статус'">
                <span class="link" v-if="editable" @click="statusChange">{{ datasource.data.status }}</span>
                <span v-else>{{ datasource.data.status }}</span>
            </value>
        </container>

        <container w-50 mt-30 inline pl-20 v-if="datasource.data.images && datasource.data.images[0]">
            <img class="w-100" :src="datasource.data.images[0]" :alt="datasource.data.name"/>
        </container>

        <container w-100 mt-30>
            <value-area :title="'Краткое описание экскурсии'">{{ datasource.data.announce }}</value-area>
            <value-area :title="'Полное описание экскурсии'">{{ datasource.data.description }}</value-area>
        </container>

        <container mt-15 v-if="editable">
            <base-link-button :to="{ name: 'excursion-edit', params: { id: excursionId }}">Редактировать
            </base-link-button>
        </container>

        <pop-up ref="popup" v-if="editable"
                :manual="true"
                :title="'Изменить статус экскурсии'"
                :buttons="[
                    {result: 'no', caption: 'Отмена', color: 'white'},
                    {result: 'yes', caption: 'OK', color: 'green'}
                ]"
        >
            <dictionary-drop-down
                :dictionary="'excursion_statuses'"
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

export default {
    mixins: [UseBaseTableBundle],

    props: {
        excursionId: {type: Number, required: true},
        datasource: {type: Object},
        editable: {type: Boolean, default: false},
    },

    components: {
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
                        axios.post('/api/excursions/status', {id: this.excursionId, status_id: this.current_status})
                            .then(response => {
                                this.$toast.success(response.data.data.message, 2000);
                                this.datasource.data.status = response.data.data.status;
                                this.datasource.data.status_id = response.data.data.status_id;
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
