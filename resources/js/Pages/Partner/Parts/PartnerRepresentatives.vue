<template>
    <GuiContainer w-100>
        <ListTable v-if="positions && positions.length > 0" :titles="['ФИО представителя', 'Должность', 'Рабочий телефон, email', 'Статус доступа']">
            <ListTableRow v-for="position in positions">
                <ListTableCell>
                    {{ position['user'] }}
                </ListTableCell>
                <ListTableCell>
                    {{ position['title'] }}
                </ListTableCell>
                <ListTableCell>
                    <div>
                        <a class="link" :href="'mailto:' + position['email']" target="_blank">{{ position['email'] }}</a>
                    </div>
                    <div>{{ position['work_phone'] }}<span v-if="position['work_phone_additional']"> доб. {{ position['work_phone_additional'] }}</span>
                    </div>
                </ListTableCell>
                <ListTableCell>
                    <GuiAccessIndicator :locked="!position['active']"/>
                    {{ position['status'] }}
                </ListTableCell>
            </ListTableRow>
        </ListTable>

        <GuiMessage border v-else>Нет прикреплённых представителей</GuiMessage>
    </GuiContainer>
</template>

<script>
import GuiContainer from "@/Components/GUI/GuiContainer";
import ListTable from "@/Components/ListTable/ListTable";
import ListTableRow from "@/Components/ListTable/ListTableRow";
import ListTableCell from "@/Components/ListTable/ListTableCell";
import GuiAccessIndicator from "@/Components/GUI/GuiAccessIndicator";
import GuiMessage from "@/Components/GUI/GuiMessage";

export default {
    props: {
        positions: {type: Array, default: () => ([])},
    },

    components: {
        GuiMessage,
        GuiAccessIndicator,
        ListTableCell,
        ListTableRow,
        ListTable,
        GuiContainer,
    },
}
</script>
