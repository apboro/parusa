<template>
    <container mt-20 mb-20>
        <message>Здесь будет баланс лицевого счёта</message>
    </container>
    <page>
        <template v-slot:header>
            <page-title-bar
                :title="'Тарифы и комисионное вознаграждение'"
            >
            </page-title-bar>
        </template>
        <partner-ticket-rates :partner-id="0" :editable="false"/>
    </page>
</template>

<script>
import Page from "../../Layouts/Page";
import Message from "../../Layouts/Parts/Message";
import PartnerTicketRates from "../../Parts/Partners/Partner/PartnerTicketRates";
import Container from "../../Components/GUI/Container";
import Heading from "../../Components/GUI/Heading";
import PageTitleBar from "../../Layouts/Parts/PageTitleBar";

export default {
    components: {
        PageTitleBar,
        Heading,
        Container,
        PartnerTicketRates,
        Message,
        Page,
    }
}
</script>
