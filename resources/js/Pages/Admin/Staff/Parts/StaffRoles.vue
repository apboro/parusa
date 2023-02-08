<template>
    <LoadingProgress :loading="!ready">
        <GuiContainer mt-15 pl-15>
            <template v-for="(role, key) in allRoles">
                <InputCheckbox v-if="role['enabled']"
                               :key="key"
                               :disabled="!editable"
                               v-model="roles"
                               :label="role['name']"
                               :value="role['id']"
                />
            </template>
            <GuiContainer v-if="editable" mt-20>
                <GuiButton :color="'green'" @click="apply">Применить</GuiButton>
            </GuiContainer>
        </GuiContainer>
    </LoadingProgress>
</template>

<script>
import InputCheckbox from "@/Components/Inputs/InputCheckbox";
import GuiButton from "@/Components/GUI/GuiButton";
import GuiContainer from "@/Components/GUI/GuiContainer";
import LoadingProgress from "@/Components/LoadingProgress";

export default {
    components: {LoadingProgress, GuiContainer, GuiButton, InputCheckbox},
    props: {
        staffId: {type: Number, required: true},
        data: {type: Object, required: true},
        editable: {type: Boolean, default: false},
    },

    watch: {
        data(value) {
            this.roles = value['roles'];
        },
    },

    data: () => ({
        roles: [],
        updating: false,
    }),

    computed: {
        allRoles() {
            return this.$store.getters['dictionary/ready']('roles') ? this.$store.getters['dictionary/dictionary']('roles') : [];
        },
        ready() {
            return this.$store.getters['dictionary/ready']('roles') && !this.updating;
        }
    },

    created() {
        if (!this.$store.getters['dictionary/ready']('roles')) {
            this.$store.dispatch('dictionary/refresh', 'roles');
        }
        this.roles = this.data ? this.data['roles'] : [];
    },

    methods: {
        apply() {
            this.updating = true;
            axios.post('/api/staff/properties', {id: this.staffId, data: {name: 'roles', value: this.roles}})
                .then(response => {
                    this.$emit('update', response.data.payload);
                    this.$toast.success(response.data['message'], 3000);
                })
                .catch(error => {
                    this.$toast.error(error.response.data['message']);
                })
                .finally(() => {
                    this.updating = false;
                })
        },
    }
}
</script>
