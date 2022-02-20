<template>
    <FieldText
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
        :small="small"
        @change="change"
        ref="input"
    />
</template>

<script>
import FormMixin from "@/Components/Form/Helpers/FormMixin";
import FieldText from "@/Components/Fields/FieldText";

export default {
    components: {FieldText},
    props: {
        disabled: {type: Boolean, default: false},
        small: {type: Boolean, default: false},
    },

    mixins: [FormMixin],
}
</script>
