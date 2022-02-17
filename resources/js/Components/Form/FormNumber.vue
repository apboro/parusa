<template>
    <FieldNumber
        :model-value="value"
        :name="name"
        :title="title"
        :hide-title="hideTitle"
        :original="original"
        :valid="valid"
        :errors="errors"
        :required="required"
        :disabled="disabled"
        :placeholder="placeholder"
        :step="step"
        :quantity="quantity"
        :small="small"
        @change="change"
        ref="input"
    />
</template>

<script>
import FieldNumber from "@/Components/Fields/FieldNumber";
import FormMixin from "@/Components/Form/Helpers/FormMixin";

export default {
    components: {FieldNumber},
    props: {
        disabled: {type: Boolean, default: false},
        placeholder: {type: String, default: null},
        small: {type: Boolean, default: false},
        step: {type: Number, default: 1},
        quantity: {type: Boolean, default: false},
    },

    mixins: [FormMixin],
}
</script>

