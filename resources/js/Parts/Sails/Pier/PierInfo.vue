<template>
    <base-table-container :w="'50'">
        <base-table :borders="false" :highlight="false" :hover="false" :small="true">
            <base-table-row>
                <base-table-cell :w="'200'">Название</base-table-cell>
                <base-table-cell>{{ datasource.data.name }}</base-table-cell>
            </base-table-row>
            <base-table-row>
                <base-table-cell :w="'200'">Адрес причала</base-table-cell>
                <base-table-cell>{{ datasource.data.address }}</base-table-cell>
            </base-table-row>
            <base-table-row>
                <base-table-cell :w="'200'">Время работы</base-table-cell>
                <base-table-cell>{{ datasource.data.work_time }}</base-table-cell>
            </base-table-row>
            <base-table-row>
                <base-table-cell :w="'200'">Координаты причала</base-table-cell>
                <base-table-cell>{{ datasource.data.latitude }}, {{ datasource.data.longitude }}</base-table-cell>
            </base-table-row>
            <base-table-row>
                <base-table-cell :w="'200'">Статус</base-table-cell>
                <base-table-cell>{{ datasource.data.status }}</base-table-cell>
            </base-table-row>
        </base-table>
    </base-table-container>

    <div class="container-50 w-50 mt-30px" v-if="datasource.data.images">
    <img class="w-100" :src="datasource.data.images[0]" :alt="datasource.data.name"/>
    </div>

    <text-container class="mt-30px" :title="'Описание причала'">{{ datasource.data.description }}</text-container>
    <text-container :title="'Как добраться'">{{ datasource.data.way_to }}</text-container>



    <container :no-bottom="true">
        <base-link-button :to="{ name: 'pier-edit', params: { id: pierId }}">Редактировать
        </base-link-button>
    </container>
</template>

<script>
import UseBaseTableBundle from "../../../Mixins/UseBaseTableBundle";
import Container from "../../../Layouts/Parts/Container";
import BaseLinkButton from "../../../Components/Base/BaseLinkButton";
import TextContainer from "../../../Layouts/Parts/TextContainer";

export default {
    mixins: [UseBaseTableBundle],

    props: {
        pierId: {type: Number, required: true},
        datasource: {type: Object},
    },

    components: {
        TextContainer,
        Container,
        BaseLinkButton,
    },
}
</script>
