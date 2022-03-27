<template>
    <FieldFiles
        :model-value="value"
        :name="name"
        :title="title"
        :hide-title="hideTitle"
        :original="original"
        :valid="valid"
        :errors="errors"
        :required="required"
        :disabled="disabled"
        :max-files="maxFiles"
        @change="change"
        ref="input"
    />
</template>

<script>
import FormMixin from "@/Components/Form/Helpers/FormMixin";
import FieldFiles from "@/Components/Fields/FieldFiles";

export default {
    components: {FieldFiles},
    props: {
        disabled: {type: Boolean, default: false},
    },

    mixins: [FormMixin],

    computed: {
        maxFiles() {
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
