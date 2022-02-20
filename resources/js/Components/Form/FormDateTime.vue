<template>
    <FieldDateTime
        :model-value="value"
        :name="name"
        :title="title"
        :hide-title="hideTitle"
        :original="original"
        :valid="valid"
        :errors="errors"
        :required="required"
        :disabled="disabled"
        :date-disabled="dateDisabled"
        :time-disabled="timeDisabled"
        :from="from"
        :to="to"
        :clearable="clearable"
        :pick-on-clear="pickOnClear"
        :placeholder="title"
        :small="small"
        @change="change"
    />
</template>

<script>
import FieldDateTime from "@/Components/Fields/FieldDateTime";
import FormMixin from "@/Components/Form/Helpers/FormMixin";

export default {
    components: {FieldDateTime},
    props: {
        disabled: {type: Boolean, default: false},
        dateDisabled: {type: Boolean, default: false},
        timeDisabled: {type: Boolean, default: false},

        small: {type: Boolean, default: false},

        from: {type: String, default: null},
        to: {type: String, default: null},
        clearable: {type: Boolean, default: false},
        pickOnClear: {type: Boolean, default: true},
    },

    mixins: [FormMixin],
}
</script>

