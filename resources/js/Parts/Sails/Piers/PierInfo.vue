<template>
    <div>
        <container w-50 mt-30 inline>
            <value :title="'Название'">{{ datasource.data['name'] }}</value>
            <value :title="'Адрес причала'">{{ datasource.data['address'] }}</value>
            <value :title="'Время работы'">{{ datasource.data['work_time'] }}</value>
            <value :title="'Координаты причала'">
                <span v-if="datasource.data['latitude'] && datasource.data['longitude']">{{ datasource.data['latitude'] }}, {{ datasource.data['longitude'] }}</span>
            </value>
            <value :title="'Статус'">
                <span class="link" v-if="editable" @click="statusChange"><activity :active="datasource.data['active']"/>{{ datasource.data['status'] }}</span>
                <span v-else><activity :active="datasource.data['active']"/>{{ datasource.data['status'] }}</span>
            </value>
        </container>

        <container w-50 mt-30 inline pl-20 v-if="datasource.data['images'] && datasource.data['images'][0]">
            <img class="w-100" :src="datasource.data['images'][0]" :alt="datasource.data['name']"/>
        </container>

        <container w-100 mt-30>
            <value-area :title="'Описание причала'" v-text="datasource.data['description']"/>
            <value-area :title="'Как добраться'" v-text="datasource.data['way_to']"/>
        </container>

        <container mt-15 v-if="editable">
            <base-link-button :to="{ name: 'pier-edit', params: { id: pierId }}">Редактировать</base-link-button>
        </container>

        <pop-up ref="popup"
                :title="'Изменить статус причала'"
                :buttons="[{result: 'no', caption: 'Отмена', color: 'white'}, {result: 'yes', caption: 'OK', color: 'green'}]"
                :manual="true"
        >
            <dictionary-drop-down :dictionary="'pier_statuses'" v-model="current_status" :name="'status'" :original="initial_status"/>
        </pop-up>
    </div>
</template>

<script>
import UseBaseTableBundle from "../../../Mixins/UseBaseTableBundle";

import Container from "../../../Components/GUI/Container";
import Value from "../../../Components/GUI/Value";
import Activity from "../../../Components/Activity";
import ValueArea from "../../../Components/GUI/ValueArea";
import BaseLinkButton from "../../../Components/Base/BaseLinkButton";
import PopUp from "../../../Components/PopUp";
import DictionaryDropDown from "../../../Components/Dictionary/DictionaryDropDown";

export default {
    mixins: [UseBaseTableBundle],

    props: {
        pierId: {type: Number, required: true},
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
                        axios.post('/api/piers/status', {id: this.pierId, status_id: this.current_status})
                            .then(response => {
                                this.$toast.success(response.data.message, 3000);
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
