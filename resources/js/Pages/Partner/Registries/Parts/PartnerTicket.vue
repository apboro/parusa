<template>
    <LayoutPage :title="title" :loading="info.is_loading">
        <GuiContainer w-70>
            <GuiValue :title="isReserve ? 'Номер брони' : 'Номер заказа'">
                <router-link class="link" :to="{name: 'order-info', params: {id: info.data['order_id'] }}" v-if="info.is_loaded">{{ info.data['order_id'] }}</router-link>
            </GuiValue>
            <GuiValue :title="isReserve ? 'Дата и время бронирования' : 'Дата и время продажи'">{{ info.data['sold_at'] }}</GuiValue>
            <GuiValue :title="'Способ продажи'" v-if="!isReserve">{{ info.data['order_type'] }}</GuiValue>
            <GuiValue :title="isReserve ? 'Кем забронировано' : 'Продавец'">{{ info.data['position'] ? info.data['position'] + ', ' : '' }} {{ info.data['partner'] }}</GuiValue>
            <GuiValue :title="'Статус'">{{ info.data['status'] }}</GuiValue>
        </GuiContainer>

        <GuiHeading mt-30 mb-30>Информация о билете</GuiHeading>
        <GuiContainer w-70>
            <GuiValue :title="'Экскурсия'">{{ info.data['excursion'] }}</GuiValue>
            <GuiValue :title="'Причал'">{{ info.data['pier'] }}</GuiValue>
            <GuiValue :title="'Дата и время отправления'">{{ info.data['trip_start_date'] }}, {{ info.data['trip_start_time'] }} (рейс №{{ info.data['trip_id'] }})</GuiValue>
            <GuiValue :title="'Тип билета'">{{ info.data['grade'] }}</GuiValue>
            <GuiValue :title="'Цена'">{{ info.data['base_price'] }} руб.</GuiValue>
        </GuiContainer>

        <GuiHeading mt-30 mb-30>Информация о плательщике</GuiHeading>

        <GuiContainer w-70 mb-50>
            <GuiValue :title="'Имя'">{{ info.data['name'] }}</GuiValue>
            <GuiValue :title="'Email'">{{ info.data['email'] }}</GuiValue>
            <GuiValue :title="'Телефон'">{{ info.data['phone'] }}</GuiValue>
        </GuiContainer>

        <template v-if="info.is_loaded && !isReserve">
            <GuiContainer>
                <GuiButton @clicked="downloadTicket">Скачать билет в PDF</GuiButton>
                <GuiButton @clicked="emailTicket" :disabled="!info.data['email']">Отправить клиенту на почту</GuiButton>
                <GuiButton @clicked="printTicket">Распечатать</GuiButton>
            </GuiContainer>
        </template>
    </LayoutPage>
</template>

<script>
import data from "@/Core/Data";
import LayoutPage from "@/Components/Layout/LayoutPage";
import GuiContainer from "@/Components/GUI/GuiContainer";
import GuiValue from "@/Components/GUI/GuiValue";
import GuiHeading from "@/Components/GUI/GuiHeading";
import GuiButton from "@/Components/GUI/GuiButton";
import {saveAs} from "file-saver";
import printJS from 'print-js';

export default {
    components: {
        GuiButton,
        GuiHeading,
        GuiValue,
        GuiContainer,
        LayoutPage,
    },

    props: {
        ticketId: {type: Number, required: true},
    },

    data: () => ({
        info: data('/api/registries/ticket'),
    }),

    computed: {
        title() {
            return 'Билет' + ' №' + this.ticketId;
        },
        isReserve() {
            return this.info.data['is_order_reserve'];
        }
    },

    created() {
        this.info.load({id: this.ticketId})
    },

    methods: {
        downloadTicket() {
            axios.post('/api/registries/ticket/download', {id: this.ticketId})
                .then(response => {
                    let ticket = atob(response.data.data['ticket']);
                    let byteNumbers = new Array(ticket.length);
                    for (let i = 0; i < ticket.length; i++) {
                        byteNumbers[i] = ticket.charCodeAt(i);
                    }
                    let byteArray = new Uint8Array(byteNumbers);
                    let blob = new Blob([byteArray], {type: "application/pdf;charset=utf-8"});

                    saveAs(blob, response.data.data['file_name'], {autoBom: true});
                })
                .catch(error => {
                    this.$toast.error(error.response.data['message']);
                });
        },
        emailTicket() {
            this.$dialog.show('Отправить билет на почту "' + this.info.data['email'] + '"?', 'question', 'orange', [
                this.$dialog.button('yes', 'Продолжить', 'orange'),
                this.$dialog.button('no', 'Отмена', 'blue'),
            ]).then(result => {
                if (result === 'yes') {
                    axios.post('/api/registries/ticket/send', {id: this.ticketId})
                        .then(response => {
                            this.$toast.success(response.data['message']);
                        })
                        .catch(error => {
                            this.$toast.error(error.response.data['message']);
                        });
                }
            });
        },
        printTicket() {
            axios.post('/api/registries/ticket/download', {id: this.ticketId})
                .then(response => {
                    let ticket = atob(response.data.data['ticket']);
                    let byteNumbers = new Array(ticket.length);
                    for (let i = 0; i < ticket.length; i++) {
                        byteNumbers[i] = ticket.charCodeAt(i);
                    }
                    let byteArray = new Uint8Array(byteNumbers);
                    let blob = new Blob([byteArray], {type: "application/pdf;charset=utf-8"});
                    let pdfUrl = URL.createObjectURL(blob);
                    printJS(pdfUrl);
                })
                .catch(error => {
                    this.$toast.error(error.response.data['message']);
                });
        },
    }
}
</script>
