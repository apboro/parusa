<template>
    <LoadingProgress :loading="data.is_loading">
        <GuiHint mx-10 my-15>Тарифы и комиссионное вознаграждение партнёра за продажу услуг компании "Алые Паруса"</GuiHint>

        <template v-for="excursion in data.payload['excursions']" v-if="data.payload['excursions'] && data.payload['excursions'].length > 0">
            <div class="flex mt-30 mb-10" style="align-items: center">
                <GuiHeading bold>{{ excursion['name'] }}</GuiHeading>
                <InputCheckbox :label="'Отображать экскурсию в витрине'" style="width: 390px"
                               :modelValue="excursion['partner_showcase_hide_count'] === 0"
                               @change="$event => changeVisibility(excursion, $event)"
                />
            </div>
            <GuiContainer pl-10>
                <div class="rate-wrapper">
                    <div class="rate-wrapper__hidden" v-if="excursion['partner_showcase_hide_count'] !== 0">
                        <IconEyeSlash/>
                    </div>
                    <GuiMessage border mt-15 v-if="currentRate(excursion) === null">Тарифы не заданы</GuiMessage>
                    <TicketRatePartner v-else :rate="currentRate(excursion)"
                                :overridable="true"
                                :hints="false"
                                :min-max="false"
                    />
                </div>
            </GuiContainer>
        </template>
    </LoadingProgress>
</template>

<script>
import data from "@/Core/Data";
import LoadingProgress from "@/Components/LoadingProgress";
import GuiHint from "@/Components/GUI/GuiHint";
import GuiHeading from "@/Components/GUI/GuiHeading";
import TicketRate from "@/Pages/Parts/Rates/TicketRate";
import GuiMessage from "@/Components/GUI/GuiMessage";
import GuiContainer from "@/Components/GUI/GuiContainer";
import InputCheckbox from "@/Components/Inputs/InputCheckbox";
import IconEyeSlash from "@/Components/Icons/IconEyeSlash";
import TicketRatePartner from "@/Pages/Parts/Rates/TicketRatePartner.vue";

export default {
    components: {
        TicketRatePartner,
        IconEyeSlash,
        InputCheckbox,
        GuiContainer,
        GuiMessage,
        TicketRate,
        GuiHeading,
        GuiHint,
        LoadingProgress
    },

    data: () => ({
        data: data('/api/rates'),
        visibility_changing: false,
    }),

    computed: {
        processing() {
            return this.data.is_loading;
        },
    },

    created() {
        this.data.load();
    },

    methods: {
        currentRate(excursion) {
            let current = null;
            if (Object.keys(this.data.data).length > 0) {
                this.data.data.some(rate => {
                    if (rate['excursion_id'] === excursion['id'] && rate['current']) {
                        current = rate;
                        return true;
                    }
                    return false;
                });
            }
            return current;
        },

        changeVisibility(excursion, visible) {
            excursion['partner_showcase_hide_count'] = visible ? 0 : 1;

            let confirmation = !visible ? 'Отключить ' : 'Включить ';
            confirmation += 'отображение экскурсии "' + excursion['name'] + '" в витрине?';
            this.$dialog.show(confirmation, 'question', 'red', [
                this.$dialog.button('yes', !visible ? 'Отключить' : 'Включить', 'red'),
                this.$dialog.button('no', 'Отмена', 'blue'),
            ]).then(result => {
                if (result === 'yes') {
                    // visibility changing logic
                    this.visibility_changing = true;
                    axios.post('/api/excursions/visibility', {excursion_id: excursion['id'], visible: visible})
                        .then((response) => {
                            this.visibility_changing = false;
                            this.$toast.success(response.data.message, 5000);
                            this.data.load();
                        })
                        .catch(error => {
                            this.visibility_changing = false;
                            this.$toast.error(error.response.data.message, 5000);
                            this.data.load();
                        });
                } else {
                    this.data.payload['excursions'].some((item, key) => {
                        if (item['id'] === excursion['id']) {
                            this.data.payload['excursions'][key]['partner_showcase_hide_count'] = visible ? 1 : 0;
                        }
                        return false;
                    })
                }
            });
        }
    }
}
</script>

<style lang="scss" scoped>
.rate-wrapper {
    position: relative;

    &__hidden {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: transparentize(#c9c9c9, 0.75);
        display: flex;
        justify-content: center;
        align-items: center;

        & > svg {
            width: 100px;
            height: 100px;
            opacity: 0.25;
        }
    }
}
</style>
