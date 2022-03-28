<template>
    <div>
        <GuiContainer w-50 mt-30>
            <GuiValue :title="'Название партнера'">{{ data['name'] }}</GuiValue>
            <GuiValue :title="'Тип партнера'">{{ data['type'] }}</GuiValue>
            <GuiValue :title="'Статус'">
                <GuiActivityIndicator :active="data['active']"/>
                {{ data['status'] }}
            </GuiValue>
        </GuiContainer>
        <GuiContainer w-50 mt-30>
            <GuiValue :title="'Билеты для гидов'">
                <span>{{ data['tickets_for_guides'] }}</span>
            </GuiValue>
            <GuiHint mt-5 mb-10>
                При значении "0" партнер не может включать в заказ бесплатные билеты для гидов. Любое положительное число разрешает данную возможность и определяет
                максимальное количество таких билетов для одного заказа. Например, при значении "1" к заказу можно будет добавить 1 билет для гида.
            </GuiHint>
        </GuiContainer>

        <GuiContainer w-100 mt-30>
            <GuiValueArea :title="'Документы'">
                <GuiFilesList :files="data['documents']"/>
            </GuiValueArea>
        </GuiContainer>
    </div>
</template>

<script>
import GuiContainer from "@/Components/GUI/GuiContainer";
import GuiValue from "@/Components/GUI/GuiValue";
import GuiValueArea from "@/Components/GUI/GuiValueArea";
import GuiActivityIndicator from "@/Components/GUI/GuiActivityIndicator";
import GuiHint from "@/Components/GUI/GuiHint";
import GuiFilesList from "@/Components/GUI/GuiFilesList";

export default {
    props: {
        data: {type: Object},
    },

    components: {
        GuiFilesList,
        GuiHint,
        GuiActivityIndicator,
        GuiValueArea,
        GuiValue,
        GuiContainer
    },
}
</script>
