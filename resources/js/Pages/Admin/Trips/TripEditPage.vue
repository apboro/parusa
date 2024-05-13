<template>
    <LayoutPage :loading="processing" :title="form.payload['title']"
                :breadcrumbs="[{caption: 'Рейсы', to: {name: 'trip-list'}}]"
                :link="{name: 'trip-list'}"
                :link-title="'К списку рейсов'"
    >
        <GuiContainer mt-20 w-700px>
            <FormDictionary :form="form" :name="'start_pier_id'" :dictionary="'piers'" :fresh="true" :search="true"
                            @change="startPierChanged"/>
            <FormDateTime :form="form" :name="'start_at'" :to="start_end_match ? null : form.values['end_at']"
                          :clearable="true" :pick-on-clear="true" @change="startDateChanged"
                          :date-disabled="tripId !== 0"
            />
            <div v-for="(pier, index) in middle_piers" :key="index">
                <div style="background: rgb(250 247 247);; margin: 10px 0 10px 0;">
                    <FormDictionary
                        :form="form"
                        :title="'Промежуточный причал '+ [++index]"
                        :placeholder="'Выберите причал'"
                        :name="'middle_pier_id_'+[index]"
                        :dictionary="'piers'"
                        :fresh="true"
                        :search="true"
                        @change="startPierChanged(index)"
                    />
                    <FormDateTime
                        :form="form"
                        :title="'Время прибытия'"
                        :name="'middle_start_at_'+[index]"
                        :from="form.values['start_at']"
                        :clearable="true"
                        :pick-on-clear="true"
                        @change="startDateChanged(index)"
                        :date-disabled="true"
                    />
                    <FormNumber
                        :form="form"
                        :name="'middle_terminal_price_delta_'+[index]"
                        :placeholder="'Введите число'"
                        :title="'Скидка от цены кассы, %'"
                    />
                    <FormNumber
                        :form="form"
                        :name="'middle_partner_price_delta_'+[index]"
                        :placeholder="'Введите число'"
                        :title="'Скидка от цены партнера, %'"
                    />
                    <FormNumber
                        :form="form"
                        :name="'middle_site_price_delta_'+[index]"
                        :placeholder="'Введите число'"
                        :title="'Скидка от цены витрины, %'"
                    />
                </div>
            </div>

            <GuiContainer mt-5 mb-10>
                <InputCheckbox v-model="start_end_match" :label="'Дата и причал прибытия совпадают с отправлением'"
                               @change="matchModeChanged"/>
            </GuiContainer>
            <FormDictionary :form="form" :name="'end_pier_id'" :dictionary="'piers'" :fresh="true" :search="true"
                            :disabled="start_end_match"/>
            <FormDateTime :form="form" :name="'end_at'" :from="form.values['start_at']"
                          :clearable="true" :pick-on-clear="true"
                          :date-disabled="start_end_match || tripId !== 0"
            />
        </GuiContainer>


        <GuiContainer mt-20 w-700px>
            <FormDictionary :form="form" :name="'ship_id'" :dictionary="'ships'" :fresh="true" :search="true"
                            @change="shipSelected"/>
            <FormDictionary :form="form" :name="'excursion_id'" :dictionary="'excursions'" :fresh="true"
                            :search="true"/>
        </GuiContainer>

        <GuiContainer mt-20 w-700px>
            <FormNumber :form="form" :name="'tickets_total'"/>
            <FormDictionary :form="form" :name="'discount_status_id'" :dictionary="'trip_discount_statuses'"
                            :fresh="true"/>
            <FormNumber :form="form" :name="'cancellation_time'"/>
        </GuiContainer>

        <GuiContainer mt-30 w-700px v-if="tripId === 0">
            <GuiHeading mb-15>Повторять рейс</GuiHeading>
            <FieldDropDown v-model="create_mode"
                           :title="'Повторять рейс'"
                           :options="[{id: 'single', name: 'Без повторов'},{id: 'range', name: 'Каждый день'},{id: 'weekly', name: 'Недельная сетка'}]"
                           :identifier="'id'"
                           :show="'name'"
                           :top="true"
            />
            <FieldDaysOfWeek v-model="create_days" v-if="create_mode === 'weekly'" :title="'Дни'" :valid="!days_error"
                             @change="daysChanged"/>
            <FieldDate v-model="create_until" v-if="create_mode === 'range' || create_mode === 'weekly'"
                       :title="'Повторять до (включительно)'"
                       :from="form.values['start_at']"
                       :valid="!date_error"
                       @change="dateEditToChanged"
            />
        </GuiContainer>

        <GuiContainer mt-30 w-700px v-else-if="chained">
            <GuiHeading mb-15>Связанные рейсы</GuiHeading>
            <InputCheckbox v-model="edit_chained" :label="'Применить корректировки для связанных рейсов'"
                           @change="editModeChanged"/>
            <GuiContainer mt-15 v-if="edit_chained">
                <FieldWrapper :title="'Изменить в диапазоне'">
                    <GuiContainer w-50 pr-5 flex>
                        <GuiText mr-5 pt-10>От:</GuiText>
                        <InputDate v-model="edit_from" :disabled="true"/>
                    </GuiContainer>
                    <GuiContainer w-50 pl-5 flex>
                        <GuiText mr-5 pt-10>До:</GuiText>
                        <InputDate v-model="edit_to" :from="edit_from" :to="edit_max" @change="dateEditToChanged"
                                   :valid="!date_error"/>
                    </GuiContainer>
                </FieldWrapper>
                <GuiText mt-20 mb-20>
                    Полный диапазон рейсов: <b>{{ chain_trips_start_at }}</b> - <b>{{ chain_trips_end_at }}</b>
                </GuiText>
            </GuiContainer>
        </GuiContainer>

        <GuiText text-red mt-10 v-if="blockedBy">
            <div class="mb-5">{{ blockedReason }}</div>
            <span v-html="blockedBy" v-if="edit_chained"></span>
        </GuiText>

        <GuiContainer mt-30 w-700px>
            <GuiButton @clicked="save" :color="'green'" :disabled="!can_edit">{{
                    tripId === 0 ? 'Добавить' : 'Применить'
                }}{{ buttonCount ? ' (' + buttonCount + ')' : '' }}
            </GuiButton>
            <GuiButton @click="cancel">Отмена</GuiButton>
            <GuiButton @click="addPier">Добавить причал</GuiButton>
        </GuiContainer>

    </LayoutPage>
</template>

<script>
import form from "@/Core/Form";
import LayoutPage from "@/Components/Layout/LayoutPage";
import GuiContainer from "@/Components/GUI/GuiContainer";
import GuiButton from "@/Components/GUI/GuiButton";
import GuiHeading from "@/Components/GUI/GuiHeading";
import FormDictionary from "@/Components/Form/FormDictionary";
import FormDateTime from "@/Components/Form/FormDateTime";
import FormNumber from "@/Components/Form/FormNumber";
import InputCheckbox from "@/Components/Inputs/InputCheckbox";
import FieldDropDown from "@/Components/Fields/FieldDropDown";
import FieldDaysOfWeek from "@/Components/Fields/FieldDaysOfWeek";
import FieldDate from "@/Components/Fields/FieldDate";
import FieldWrapper from "@/Components/Fields/Helpers/FieldWrapper";
import GuiText from "@/Components/GUI/GuiText";
import InputDate from "@/Components/Inputs/InputDate";

export default {
    components: {
        InputDate,
        GuiText,
        FieldWrapper,
        FieldDate,
        FieldDaysOfWeek,
        FieldDropDown,
        InputCheckbox,
        FormNumber,
        FormDateTime,
        FormDictionary,
        GuiHeading,
        GuiButton,
        GuiContainer,
        LayoutPage,
    },

    data: () => ({
        form: form('/api/trips/get', '/api/trips/update', {}),
        start_end_match: true,
        loading_info: false,

        middle_piers: [],

        create_mode: 'single',
        create_days: [],
        create_until: null,

        edit_chained: false,
        edit_from: null,
        edit_to: null,
        edit_max: null,

        chained: false,
        chain_trips_end_at: null,
        chain_trips_start_at: null,

        can_edit: false,
        count: null,
        blocked_by: [],

        date_error: false,
        days_error: false,
    }),

    computed: {
        tripId() {
            return Number(this.$route.params.id);
        },
        processing() {
            return this.form.is_loading || this.form.is_saving || !this.ready || this.loading_info;
        },
        ready() {
            return this.$store.getters['dictionary/ready']('piers') !== null &&
                this.$store.getters['dictionary/ready']('ships') !== null &&
                this.$store.getters['dictionary/ready']('excursions') !== null &&
                this.$store.getters['dictionary/ready']('trip_statuses') !== null &&
                this.$store.getters['dictionary/ready']('trip_sale_statuses') !== null &&
                this.$store.getters['dictionary/ready']('trip_discount_statuses') !== null;
        },
        buttonCount() {
            if (!this.edit_chained && this.create_mode === 'single') {
                return null;
            } else if (this.edit_chained) {
                return this.count;
            } else if (this.create_until && this.form.values['start_at']) {
                let start = new Date(this.form.values['start_at']);
                let end = new Date(this.create_until);
                start.setHours(0);
                start.setMinutes(0);
                start.setSeconds(0);
                start.setMilliseconds(0);
                end.setHours(0);
                end.setMinutes(0);
                end.setSeconds(0);
                end.setMilliseconds(0);
                if (start > end) {
                    return null;
                }
                if (this.create_mode === 'range' && this.create_until && this.form.values['start_at']) {
                    const diff = end.getTime() - start.getTime();
                    return diff / (1000 * 3600 * 24) + 1;
                } else if (this.create_mode === 'weekly') {
                    let count = 0;
                    if (this.create_days.indexOf(start.getDay()) === -1) {
                        count++;
                    }
                    while (start <= end) {
                        if (this.create_days.indexOf(start.getDay()) !== -1) {
                            count++;
                        }
                        start.setDate(start.getDate() + 1);
                    }
                    return count;
                }
            }
            return null;
        },
        blockedReason() {
            return this.edit_chained ? 'В диапазоне есть рейсы с оформленными билетами:' : 'На этот рейс есть оформленные билеты';
        },
        blockedBy() {
            return Object.keys(this.blocked_by).length === 0 ? null
                : Object.keys(this.blocked_by).map(key => '<b>№' + this.blocked_by[key]['id'] + '</b> (' + this.blocked_by[key]['start_at_date'] + ')').join(', ')
        },
    },

    created() {
        this.$store.dispatch('dictionary/refresh', 'piers');
        this.$store.dispatch('dictionary/refresh', 'ships');
        this.$store.dispatch('dictionary/refresh', 'excursions');
        this.$store.dispatch('dictionary/refresh', 'trip_statuses');
        this.$store.dispatch('dictionary/refresh', 'trip_sale_statuses');
        this.$store.dispatch('dictionary/refresh', 'trip_discount_statuses');

        this.form.toaster = this.$toast;

        const query = this.$route.query;

        this.form.load({
            id: this.tripId,
            create_from: typeof query['from'] !== "undefined" && query['from'] !== null ? query['from'] : null
        })
            .then(response => {
                if (this.tripId === 0) {
                    if (typeof query['pier'] !== "undefined" && query['pier'] !== null) {
                        this.form.update('start_pier_id', Number(query['pier']));
                        this.form.update('end_pier_id', Number(query['pier']));
                    }
                    /**
                     * For future use:
                     * if(typeof query['excursion'] !== "undefined" && query['excursion'] !== null) {
                     *   response.values['excursion_id'] = Number(query['excursion']);
                     * }
                     */
                    return;
                }
                this.edit_from = response.values['start_at'];
                this.edit_to = response.values['start_at'];
                this.edit_max = response.payload['chain_end_at'];
                this.chained = response.payload['chained'];
                this.chain_trips_end_at = response.payload['chain_trips_end_at'];
                this.chain_trips_start_at = response.payload['chain_trips_start_at'];
                if (response.values['start_pier_id'] !== response.values['end_pier_id'] || (response.values['start_at'] && response.values['end_at'] && (response.values['start_at'].split('T')[0] !== response.values['end_at'].split('T')[0]))) {
                    this.start_end_match = false;
                }
            });
        if (this.tripId !== 0) {
            this.getInfo();
        } else {
            this.can_edit = true;
        }
    },

    methods: {
        addPier() {
            this.middle_piers.push(this.middle_piers.length);
        },
        save() {
            if (!this.form.validate()) {
                return;
            }
            if (this.tripId === 0 && this.create_mode !== 'single') {
                // check date not empty
                if (this.create_until === null || new Date(this.create_until) < new Date(this.form.values['start_at'].split('T')[0])) {
                    this.date_error = true;
                    return;
                }
                // check days not empty
                if (this.create_mode === 'weekly' && this.create_days.length === 0) {
                    this.days_error = true;
                    return;
                }
            }
            if (this.tripId !== 0 && this.edit_chained) {
                // check date not empty
                // check dates following
                if (this.edit_to === null || new Date(this.edit_to) < new Date(this.form.values['start_at'].split('T')[0])) {
                    this.date_error = true;
                    return;
                }
            }
            let mode = this.tripId === 0 ? this.create_mode : (this.edit_chained ? 'range' : 'single');
            let to = this.tripId === 0 ? this.create_until : this.edit_to;
            let days = this.tripId === 0 ? this.create_days : null;
            this.form.save({id: this.tripId, mode: mode, to: to, days: days, middle_piers: this.middle_piers})
                .then(() => {
                    if (this.tripId === 0) {
                        const newId = this.form.payload['id'];
                        this.$router.push({name: 'trip-view', params: {id: newId}});
                    } else {
                        this.$router.push({name: 'trip-view', params: {id: this.tripId}});
                    }
                })
        },
        cancel() {
            if (this.tripId === 0) {
                this.$router.push({name: 'trip-list'})
            } else {
                this.$router.push({name: 'trip-view', params: {id: this.tripId}})
            }
        },
        matchModeChanged() {
            if (this.start_end_match) {
                this.startPierChanged(this.form.values['start_pier_id']);
                this.startDateChanged(this.form.values['start_at']);
            }
        },
        startPierChanged(value) {
            if (this.start_end_match) {
                this.form.update('end_pier_id', value);
            }
        },
        startDateChanged(value) {
            if (this.start_end_match) {
                const startDate = value !== null ? value.split('T') : null;
                const endDate = this.form.values['end_at'] !== null ? this.form.values['end_at'].split('T') : null;
                let combined = (startDate ? startDate[0] : '') + (endDate && typeof endDate[1] !== "undefined" ? 'T' + endDate[1] : '');
                this.form.update('end_at', combined !== '' ? combined : null);
            }
        },
        shipSelected(value) {
            if (!this.$store.getters['dictionary/ready']('ships')) {
                return;
            }
            this.$store.getters['dictionary/dictionary']('ships').some(ship => {
                if (ship['id'] === value) {
                    this.form.update('tickets_total', ship['capacity'])
                }
            });
        },
        editModeChanged() {
            this.date_error = false;
            this.days_error = false;
            this.getInfo();
        },
        dateEditToChanged() {
            this.date_error = false;
            this.getInfo();
        },
        daysChanged() {
            this.days_error = false;
        },
        getInfo() {
            if (this.tripId === 0 || this.edit_chained && (
                this.edit_to === null
                || this.form.values['start_at'] === null
                || this.form.values['start_at'].split('T')[0] === ''
                || new Date(this.edit_to) < new Date(this.form.values['start_at'].split('T')[0])
            )) {
                return;
            }
            this.loading_info = true;
            axios.post('/api/trips/info', {
                id: this.tripId,
                mode: this.edit_chained ? 'range' : 'single',
                to: this.edit_to
            })
                .then(response => {
                    this.count = response.data.data['count'];
                    this.can_edit = response.data.data['operable'];
                    this.blocked_by = response.data.data['blocked_by'];
                })
                .catch(error => {
                    this.$toast.error(error.response.data.message);
                })
                .finally(() => {
                    this.loading_info = false;
                })
        },
    }
}
</script>
