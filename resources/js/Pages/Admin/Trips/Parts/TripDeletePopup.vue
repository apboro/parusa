<template>
    <PopUp ref="delete_popup" :title="title"
           :buttons="[
                    {result: 'yes', caption: 'Удалить' + (count ? ' (' + count + ')' : ''), disabled: !can_delete, color: 'red'},
                    {result: 'no', caption: 'Отмена', color: 'white'},
                   ]"
           :manual="true"
           :message="'Рейс имеет связанные рейсы. Выберите вариант удаления:'"
    >
        <GuiContainer w-400px>
            <InputDropDown v-model="mode" :identifier="'id'" :show="'name'" :options="tripDeleteOptions" @change="getInfo"/>
            <GuiContainer v-if="mode === 'range'" flex mt-30>
                <GuiContainer w-50 pr-5 flex>
                    <GuiText mr-5 pt-10>От:</GuiText>
                    <InputDate v-model="delete_from" :disabled="true"/>
                </GuiContainer>
                <GuiContainer w-50 pl-5 flex>
                    <GuiText mr-5 pt-10>До:</GuiText>
                    <InputDate v-model="delete_to" :from="delete_from" :to="delete_max" @change="getInfo"/>
                </GuiContainer>
            </GuiContainer>
            <GuiText mt-30 v-if="mode === 'all' || mode === 'range'">
                Полный диапазон рейсов: <b>{{ chain_start_date }}</b> - <b>{{ chain_end_date }}</b>
            </GuiText>
            <GuiText text-red mt-10 text-sm v-if="blockedBy">
                <div class="mb-5">Есть рейсы с оформленными билетами:</div>
                <span v-html="blockedBy"></span>
            </GuiText>
        </GuiContainer>
    </PopUp>
</template>

<script>
import PopUp from "@/Components/PopUp";
import GuiContainer from "@/Components/GUI/GuiContainer";
import InputDropDown from "@/Components/Inputs/InputDropDown";
import GuiText from "@/Components/GUI/GuiText";
import InputDate from "@/Components/Inputs/InputDate";

export default {
    components: {InputDate, GuiText, InputDropDown, GuiContainer, PopUp},

    data: () => ({
        mode: 'single',

        trip_id: null,
        trip_start_date: null,
        trip_start_time: null,
        chain_start_date: null,
        chain_end_date: null,

        delete_from: null,
        delete_to: null,
        delete_max: null,

        can_delete: true,
        count: null,
        blocked_by: [],
    }),

    computed: {
        title() {
            return 'Удаление рейса №' + this.trip_id + ' - ' + this.trip_start_date + ' ' + this.trip_start_time;
        },
        tripDeleteOptions() {
            return [
                {id: 'single', name: 'Удалить только выбранный рейс'},
                {id: 'all', name: 'Удалить все связанные рейсы'},
                {id: 'range', name: 'Удалить связанные рейсы в диапазоне'},
            ];
        },
        blockedBy() {
            return Object.keys(this.blocked_by).length === 0 ? null
                : Object.keys(this.blocked_by).map(key => '<b>№' + this.blocked_by[key]['id'] + '</b> (' + this.blocked_by[key]['start_at_date'] + ')').join(', ')
        }
    },

    methods: {
        remove(trip) {
            this.trip_id = trip['id'];
            this.trip_start_date = trip['start_date'];
            this.trip_start_time = trip['start_time'];
            this.chain_start_date = trip['chain_trips_start_at'];
            this.chain_end_date = trip['chain_trips_end_at'];
            this.delete_from = trip['_trip_start_at'];
            this.delete_to = trip['_trip_start_at'];
            this.delete_max = trip['_chain_end_at'];
            this.can_delete = true;
            this.count = null;
            this.blocked_by = [];

            return new Promise((resolve, reject) => {
                this.$refs.delete_popup.show()
                    .then(result => {
                        if (result === 'yes') {
                            this.$refs.delete_popup.process(true);
                            axios.post('/api/trips/delete', {id: this.trip_id, mode: this.mode, to: this.delete_to})
                                .then(response => {
                                    this.$toast.success(response.data.message, 5000);
                                    resolve();
                                })
                                .catch(error => {
                                    this.$toast.error(error.response.data.message, 5000);
                                    reject();
                                })
                                .finally(() => {
                                    this.$refs.delete_popup.hide();
                                    this.mode = 'single';
                                })
                        } else {
                            this.$refs.delete_popup.hide();
                            this.mode = 'single';
                        }
                    });
            });
        },
        getInfo() {
            if (this.mode === 'single') {
                this.count = null;
                this.can_delete = true;
                this.blocked_by = [];
                return;
            }
            this.$refs.delete_popup.process(true);
            axios.post('/api/trips/info', {id: this.trip_id, mode: this.mode, to: this.delete_to})
                .then(response => {
                    this.count = response.data.data['count'];
                    this.can_delete = response.data.data['operable'];
                    this.blocked_by = response.data.data['blocked_by'];
                })
                .catch(error => {
                    this.$toast.error(error.response.data.message);
                })
                .finally(() => {
                    this.$refs.delete_popup.process(false);
                })
        },
    }
}
</script>

