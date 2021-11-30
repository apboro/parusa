<template>
    <div class="login">
        <div class="login__logo"></div>
        <div class="login__divider"></div>
        <div class="login__form">
            <base-input v-model="credentials.login"
                        :title="'Логин'"
                        :name="'login'"
                        :required="true"
                        :valid="validation.login"
                        :error="'Обязательное поле'"
                        :autocomplete="'username'"
                        @changed="changed"
                        @keyup.enter="enter"
                        ref="login"
            />
            <base-input v-model="credentials.password"
                        :title="'Пароль'"
                        :name="'password'"
                        :type="'password'"
                        :required="true"
                        :valid="validation.password"
                        :error="'Обязательное поле'"
                        :autocomplete="'current-password'"
                        @changed="changed"
                        @keyup.enter="enter"
                        ref="password"
            />
            <p class="login__form-error" v-if="error">{{ error }}</p>
            <div class="login__form-buttons">
                <base-button @clicked="login">Войти</base-button>
            </div>
        </div>
    </div>
</template>

<script>
import BaseButton from "../Components/Base/BaseButton";
import BaseInput from "../Components/Base/BaseInput";
import empty from "../Helpers/Lib/empty";
import validation from "../Helpers/validation";

import axios from "axios";

export default {
    name: "LoginApp",

    components: {
        BaseButton,
        BaseInput,
    },

    mixins: [validation],

    data: () => ({
        credentials: {
            login: null,
            password: null,
        },
        validation: {
            login: true,
            password: true,
        },
        processing: false,
        error: null,
    }),
    methods: {
        changed(name, value) {
            this.setValidation(name, !empty(value))
        },

        enter() {
            if (empty(this.credentials.login)) {
                this.$refs.login.focus();
            } else if (empty(this.credentials.password)) {
                this.$refs.password.focus();
            } else {
                this.login();
            }
        },

        login() {
            if (this.processing) {
                return;
            }

            // force validation
            this.changed('login', this.credentials.login);
            this.changed('password', this.credentials.password);

            if (!this.isValid()) {
                this.enter();
                return;
            }

            axios.post('/login', this.credentials)
                .then(response => {
                    this.error = null;
                    window.location.href = (typeof response.data.redirect !== "undefined") ? response.data.redirect : '/';
                })
                .catch(error => {
                    this.error = error.response.data.errors.login.join(', ');
                })
                .finally(() => {
                    this.processing = false;
                });
        },
    }
}
</script>
