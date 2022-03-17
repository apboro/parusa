<template>
    <FieldDropDown
        :model-value="value"
        :name="name"
        :title="title"
        :hide-title="hideTitle"
        :original="original"
        :valid="valid"
        :errors="errors"
        :required="required"
        :disabled="disabled"
        :options="options"
        :has-null="hasNull"
        :identifier="identifier"
        :show="show"
        :multi="multi"
        :search="search"
        :placeholder="placeholder"
        :top="top"
        :right="right"
        :center="center"
        :small="small"
        @change="change"
        @drop="$emit('drop')"
        ref="input"
    />
</template>

<script>
import FormMixin from "@/Components/Form/Helpers/FormMixin";
import FieldDropDown from "@/Components/Fields/FieldDropDown";

export default {
    components: {FieldDropDown},
    props: {
        name: {type: String, required: true},
        disabled: {type: Boolean, default: false},

        options: {type: Array, default: () => ([])},

        placeholder: {type: String, default: null},
        hasNull: {type: Boolean, default: false},

        identifier: {type: String, default: null},
        show: {type: String, default: null},

        multi: {type: Boolean, default: false},

        search: {type: Boolean, default: false},
        top: {type: Boolean, default: false},
        right: {type: Boolean, default: false},
        center: {type: Boolean, default: false},
        small: {type: Boolean, default: false},
    },

    emits: ['drop'],

    mixins: [FormMixin],
}
</script>

