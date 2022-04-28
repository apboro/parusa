<template>
    <ShowcaseFieldNumber
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
        :step="step"
        :quantity="quantity"
        :min="min"
        :max="max"
        :small="small"
        :border="border"
        @change="change"
        ref="input"
    />
</template>

<script>
import FormMixin from "@/Components/Form/Helpers/FormMixin";
import ShowcaseFieldNumber from "@/Pages/Showcase/Components/ShowcaseFieldNumber";

export default {
    components: {ShowcaseFieldNumber},
    props: {
        disabled: {type: Boolean, default: false},
        small: {type: Boolean, default: false},
        step: {type: Number, default: 1},
        quantity: {type: Boolean, default: false},
        min: {type: Number, default: null},
        max: {type: Number, default: null},
        border: {type: Boolean, default: true},
    },

    mixins: [FormMixin],
}
</script>

