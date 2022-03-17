<template>
    <FieldDictionary
        :model-value="value"
        :name="name"
        :title="title"
        :hide-title="hideTitle"
        :original="original"
        :valid="valid"
        :errors="errors"
        :required="required"
        :disabled="disabled"
        :dictionary="dictionary"
        :fresh="fresh"
        :disabled-options="disabledOptions"
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
        ref="input"
    />
</template>

<script>
import FieldDictionary from "@/Components/Fields/FieldDictionary";
import FormMixin from "@/Components/Form/Helpers/FormMixin";

export default {
    components: {FieldDictionary},
    props: {
        name: {type: String, required: true},
        disabled: {type: Boolean, default: false},
        dictionary: {type: String, required: true},
        fresh: {type: Boolean, default: false},
        disabledOptions: {type: Boolean, default: false},
        hasNull: {type: Boolean, default: false},
        identifier: {type: String, default: 'id'},
        show: {type: String, default: 'name'},
        multi: {type: Boolean, default: false},
        search: {type: Boolean, default: false},
        placeholder: {type: String, default: null},
        top: {type: Boolean, default: false},
        right: {type: Boolean, default: false},
        center: {type: Boolean, default: false},
        small: {type: Boolean, default: false},
    },

    mixins: [FormMixin],
}
</script>

