<template>
    <LoadingProgress :loading="access_updating || form.is_saving">
        <template v-if="data['has_access']">
            <GuiValue :title="'Доступ активирован для логина'" :dots="false" :class="'w-230px'" mt-30 mb-20><b>{{ data['login'] }}</b></GuiValue>
            <GuiButton v-if="editable" @click="closeAccess" :color="'red'">Закрыть доступ в систему</GuiButton>
        </template>
        <GuiContainer v-else w-50 mt-20>
            <FormString :form="form" :name="'login'"/>
            <FormString :form="form" :name="'password'" :type="'password'"/>
            <FormString :form="form" :name="'password_confirmation'" :type="'password'"/>
            <FormCheckBox
                :form="form"
                :name="isSendEmail"
                label="Отправить доступы на e-mail"
                v-model="isSendEmail"
                @change="updateSendEmail"
                ref="input"
            />
            <FormString v-if="isSendEmail" :form="form" :name="'email'"/>
            <GuiButton v-if="editable" :class="'mt-20'" :color="'green'" @click="openAccess">Открыть доступ в систему</GuiButton>
        </GuiContainer>
    </LoadingProgress>
</template>

<script>
import form from "@/Core/Form";
import GuiContainer from "@/Components/GUI/GuiContainer";
import LoadingProgress from "@/Components/LoadingProgress";
import GuiMessage from "@/Components/GUI/GuiMessage";
import GuiValue from "@/Components/GUI/GuiValue";
import GuiButton from "@/Components/GUI/GuiButton";
import FormString from "@/Components/Form/FormString";
import FieldCheckBox from "@/Components/Fields/FieldCheckBox.vue";
import FormCheckBox from "@/Components/Form/FormCheckBox.vue";

export default {
    props: {
        representativeId: {type: Number, required: true},
        data: {type: Object, required: true},
        editable: {type: Boolean, default: false},
        isSendEmail: false,
    },

    emits: ['update'],

    components: {
        FormCheckBox,
        FieldCheckBox,
        FormString,
        GuiButton,
        GuiValue,
        GuiMessage,
        LoadingProgress,
        GuiContainer,
    },

    data: () => ({
        form: form(null, '/api/representatives/access/set'),
        access_updating: false,
    }),

    watch: {
        data() {
            this.updateForm();
        },
    },

    created() {
        this.form.toaster = this.$toast;
        this.updateForm();
    },

    methods: {
        updateSendEmail() {
            let rulesEmail = this.isSendEmail ? 'required|email|bail' : 'nullable|email|bail';

            this.form.set('email', this.data['email'], rulesEmail, 'Email', true);
            this.form.set('isSendEmail', this.isSendEmail ?? false, 'required', 'Отправить доступы на e-mail', true);
        },
        updateForm() {
            let rulesEmail = this.isSendEmail ? 'required|email|bail' : 'nullable|email|bail';

            this.form.reset();
            this.form.set('login', this.data['email'], 'required|min:6|bail', 'Логин', true);
            this.form.set('email', this.data['email'], rulesEmail, 'Email', true);
            this.form.set('password', null, 'required|min:6|bail', 'Новый пароль', true);
            this.form.set('password_confirmation', null, 'same:password', 'Подтверждение пароля', true);
            this.form.set('isSendEmail', this.isSendEmail ?? false, 'required', 'Отправить доступы на e-mail', true);
            this.form.load();
        },

        closeAccess() {
            const name = this.data['full_name'];
            this.$dialog.show('Закрыть доступ в систему для сотрудника "' + name + '"?', 'question', 'red', [
                this.$dialog.button('no', 'Отмена', 'blue'),
                this.$dialog.button('yes', 'Продолжить', 'red'),
            ]).then(result => {
                if (result === 'yes') {
                    this.access_updating = true;
                    axios.post('/api/representatives/access/release', {id: this.representativeId})
                        .then(response => {
                            this.$emit('update', response.data.payload);
                            this.updateForm();
                            this.$toast.success(response.data.message, 3000);
                        })
                        .catch(error => {
                            this.$toast.error(error.response.data.message, 5000);
                        })
                        .finally(() => {
                            this.access_updating = false;
                        });
                }
            });
        },

        openAccess() {
            if (!this.form.validate()) {
                return;
            }

            let rulesEmail = this.isSendEmail ? 'required|email|bail' : 'nullable|email|bail';

            this.form.save({id: this.representativeId})
                .then(response => {
                    this.$emit('update', response['payload']);
                    this.form.set('password', null, 'required|min:6|bail', 'Новый пароль', true);
                    this.form.set('password_confirmation', null, 'same:password', 'Подтверждение пароля', true);
                    this.form.set('email', null, rulesEmail, 'Email', true);
                    this.form.set('isSendEmail', this.isSendEmail ?? false, 'required', 'Отправить доступы на e-mail', true);
                })
        },
    }
}
</script>
