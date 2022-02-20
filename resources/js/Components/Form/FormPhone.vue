<template>
    <FieldPhone
        :model-value="value"
        :name="name"
        :title="title"
        :hide-title="hideTitle"
        :original="original"
        :valid="valid"
        :errors="errors"
        :required="required"
        :disabled="disabled"
        :placeholder="title"
        :autocomplete="autocomplete"
        :small="small"
        @change="change"
        ref="input"
    />
</template>

<script>
import FormMixin from "@/Components/Form/Helpers/FormMixin";
import FieldPhone from "@/Components/Fields/FieldPhone";

export default {
    components: {FieldPhone},
    props: {
        disabled: {type: Boolean, default: false},
        small: {type: Boolean, default: false},
        autocomplete: {type: String, default: 'off'},
    },

    mixins: [FormMixin],
}
</script>
