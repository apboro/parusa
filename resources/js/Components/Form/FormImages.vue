<template>
    <FieldImages
        :model-value="value"
        :name="name"
        :title="title"
        :hide-title="hideTitle"
        :original="original"
        :valid="valid"
        :errors="errors"
        :required="required"
        :disabled="disabled"
        :max-images="maxImages"
        @change="change"
        ref="input"
    />
</template>

<script>
import FormMixin from "@/Components/Form/Helpers/FormMixin";
import FieldImages from "@/Components/Fields/FieldImages";

export default {
    components: {FieldImages},
    props: {
        disabled: {type: Boolean, default: false},
    },

    mixins: [FormMixin],

    computed: {
        maxImages() {
            let max = 0;
            const rules = this.get('rules', {});
            if (rules !== null && typeof rules !== "undefined") {
                Object.keys(rules).some(rule => {
                    if (rule === 'max') {
                        max = rules[rule];
                        return true;
                    }
                    return false;
                });
            }
            return Number(max);
        },
    }
}
</script>
