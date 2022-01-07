<template>
    <page :loading="processing">
        <template v-slot:header>
            <page-title-bar :title="form.payload['title']"
                            :breadcrumbs="[{caption: 'Рейсы', to: {name: 'trip-list'}}]"
            />
        </template>

        <container mt-30>
            <data-field-date-time :datasource="form" :name="'start_at'" @changed="dateChanged"/>
            <data-field-date-time :datasource="form" :name="'end_at'" @changed="dateChanged"/>
            <data-field-dictionary-dropdown :datasource="form" :dictionary="'piers'" :name="'start_pier_id'" :search="true"/>
            <data-field-dictionary-dropdown :datasource="form" :dictionary="'piers'" :name="'end_pier_id'" :search="true"/>
            <data-field-dictionary-dropdown :datasource="form" :dictionary="'ships'" :name="'ship_id'" :search="true" @changed="shipSelected"/>
            <data-field-dictionary-dropdown :datasource="form" :dictionary="'excursions'" :name="'excursion_id'" :search="true"/>
            <data-field-dictionary-dropdown :datasource="form" :dictionary="'trip_statuses'" :name="'status_id'"/>
            <data-field-dictionary-dropdown :datasource="form" :dictionary="'trip_sale_statuses'" :name="'sale_status_id'"/>
            <data-field-input :datasource="form" :name="'tickets_count'" :type="'number'"/>
            <data-field-dictionary-dropdown :datasource="form" :dictionary="'trip_discount_statuses'" :name="'discount_status_id'"/>
            <data-field-input :datasource="form" :name="'cancellation_time'" :type="'number'"/>
        </container>

        <container mt-30>
            <base-button @click="save" :color="'green'">Сохранить</base-button>
            <base-button @click="$router.push({ name: 'trip-view', params: { id: this.tripId }})">Отмена</base-button>
        </container>

    </page>
</template>

<script>
import formDataSource from "../../../../Helpers/Core/formDataSource";

import Page from "../../../../Layouts/Page";
import PageTitleBar from "../../../../Layouts/Parts/PageTitleBar";
import Container from "../../../../Components/GUI/Container";
import DataFieldInput from "../../../../Components/DataFields/DataFieldInput";
import DataFieldDictionaryDropdown from "../../../../Components/DataFields/DataFieldDictionaryDropdown";
import BaseButton from "../../../../Components/Base/BaseButton";
import DataFieldDateTime from "../../../../Components/DataFields/DataFieldDateTime";

export default {
    components: {
        DataFieldDateTime,
        Page,
        PageTitleBar,
        Container,
        DataFieldInput,
        DataFieldDictionaryDropdown,
        BaseButton,
    },

    data: () => ({
        form: null,
    }),

    computed: {
        tripId() {
            return Number(this.$route.params.id);
        },
        processing() {
            return this.form.loading || this.form.saving || !this.ready;
        },
        ready() {
            return this.$store.getters['dictionary/ready']('piers') &&
                this.$store.getters['dictionary/ready']('ships') &&
                this.$store.getters['dictionary/ready']('excursions') &&
                this.$store.getters['dictionary/ready']('trip_statuses') &&
                this.$store.getters['dictionary/ready']('trip_sale_statuses') &&
                this.$store.getters['dictionary/ready']('trip_discount_statuses');
        }
    },

    created() {
        this.form = formDataSource('/api/trips/get', '/api/trips/update', {id: this.tripId});
        this.form.toaster = this.$toast;
        this.form.onLoad = this.onLoad;
        this.form.afterSave = this.afterSave;
        this.form.load();
    },

    methods: {
        save() {
            if (!this.form.validateAll()) {
                return;
            }
            this.form.save()
        },
        onLoad(values) {
            if (this.tripId !== 0) {
                return;
            }
            const query = this.$route.query;
            if (typeof query['pier'] !== "undefined" && query['pier'] !== null) {
                values['start_pier_id'] = query['pier'];
                values['end_pier_id'] = Number(query['pier']);
            }
            /**
             * For future use:
             * if(typeof query['excursion'] !== "undefined" && query['excursion'] !== null) {
             *   values['excursion_id'] = Number(query['excursion']);
             * }
             */
        },
        afterSave(payload) {
            if (this.tripId === 0) {
                const newId = payload['id'];
                this.$router.push({name: 'trip-view', params: {id: newId}});
            } else {
                this.$router.push({name: 'trip-view', params: {id: this.tripId}});
            }
        },
        shipSelected(name, value) {
            if (!this.$store.getters['dictionary/ready']('ships')) {
                return;
            }
            this.$store.getters['dictionary/dictionary']('ships').some(ship => (ship['id'] === value) && (this.form.values['tickets_count'] = ship['capacity']));
            this.form.validate('tickets_count', this.form.values['tickets_count']);
        },
        dateChanged() {
            this.form.validate('start_at', this.form.values['start_at']);
            this.form.validate('end_at', this.form.values['end_at']);
        }
    }
}
</script>
