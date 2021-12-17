<template>
    <div>
        <base-table-container :w="'50'">
            <base-table :borders="false" :highlight="false" :hover="false" :small="true">
                <base-table-row>
                    <base-table-cell :w="'200'">Название</base-table-cell>
                    <base-table-cell>{{ datasource.data.name }}</base-table-cell>
                </base-table-row>
                <base-table-row>
                    <base-table-cell :w="'200'">Тип программы</base-table-cell>
                    <base-table-cell>{{
                            datasource.data.programs ? datasource.data.programs.join(', ') : ''
                        }}
                    </base-table-cell>
                </base-table-row>
                <base-table-row>
                    <base-table-cell :w="'200'">Продолжительность</base-table-cell>
                    <base-table-cell>{{ datasource.data.duration }}</base-table-cell>
                </base-table-row>
                <base-table-row>
                    <base-table-cell :w="'200'">Статус</base-table-cell>
                    <base-table-cell>
                        <span class="link" @click="statusChange">{{ datasource.data.status }}</span>
                    </base-table-cell>
                </base-table-row>
            </base-table>
        </base-table-container>

        <div class="container-50 w-50 mt-30px" v-if="datasource.data.images">
            <img class="w-100" :src="datasource.data.images[0]" :alt="datasource.data.name"/>
        </div>

        <text-container class="mt-30px" :title="'Краткое описание экскурсии'">{{
                datasource.data.announce
            }}
        </text-container>
        <text-container :title="'Полное описание экскурсии'">{{ datasource.data.description }}</text-container>

        <container :no-bottom="true">
            <base-link-button :to="{ name: 'excursion-edit', params: { id: excursionId }}">Редактировать
            </base-link-button>
        </container>

        <pop-up ref="popup"
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
import Container from "../../../Layouts/Parts/Container";
import BaseLinkButton from "../../../Components/Base/BaseLinkButton";
import TextContainer from "../../../Layouts/Parts/TextContainer";
import PopUp from "../../../Components/PopUp";
import DictionaryDropDown from "../../../Components/Dictionary/DictionaryDropDown";

export default {
    mixins: [UseBaseTableBundle],

    props: {
        excursionId: {type: Number, required: true},
        datasource: {type: Object},
    },

    components: {
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
