<template>
    <div class="login">
        <div class="login__logo">
            <Logo/>
        </div>
        <div class="login__divider"></div>
        <div class="login__form">
            <FormString :form="form" :name="'login'" :autocomplete="'username'" @keyup.enter="enter" ref="login"/>
            <FormString :form="form" :name="'password'" :autocomplete="'current-password'" :type="'password'" @keyup.enter="enter" ref="password"/>
        </div>
        <div class="login__actions">
            <GuiButton @clicked="login">Войти</GuiButton>
        </div>
        <div class="login__actions">
            <span class="link" @click="forgot">Забыл пароль</span>
        </div>
    </div>
</template>

<script>
import form from "@/Core/Form";
import GuiButton from "@/Components/GUI/GuiButton";
import FormString from "@/Components/Form/FormString";
import Logo from "@/Apps/Logo";
import PopUp from "@/Components/PopUp";

export default {
    name: "LoginApp",

    components: {
        Logo,
        FormString,
        GuiButton,
        PopUp,
    },

    data: () => ({
        form: form(null, '/login'),
    }),

    created() {
        this.form.set('login', null, 'required', 'Логин', true);
        this.form.set('password', null, 'required', 'Пароль', true);
        this.form.load();
    },

    mounted() {
        if (typeof message !== "undefined" && message !== null) {
            this.form.errors['login'] = [message];
            this.form.valid = {login: false, password: true};
        }
    },

    methods: {
        enter() {
            if (this.form.values['login'] === null) {
                this.$refs.login.focus();
            } else if (this.form.values['password'] === null) {
                this.$refs.password.focus();
            } else {
                this.login();
            }
        },

        login() {
            if (this.form.is_saving || !this.form.validate()) {
                return;
            }

            this.form.save()
                .then(response => {
                    console.log(response)
                })
                .catch(error => {
                    if (error.code === 301) {
                        window.location.href = error.response.data.to;
                    }
                });
        },
        forgot() {
            this.$dialog.show('Чтобы восстановить доступ в систему обратитесь к администратору', 'info', 'orange', [
                this.$dialog.button('ok', 'OK', 'blue'),
            ]);
        },
    }
}
</script>

<style lang="scss">
$project_font: -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica Neue, Arial, Noto Sans, sans-serif, Apple Color Emoji, Segoe UI Emoji, Segoe UI Symbol, Noto Color Emoji !default;
$animation_time: 150ms !default;
$animation: cubic-bezier(0.24, 0.19, 0.28, 1.29) !default;
$shadow_1: 0 1px 3px rgba(0, 0, 0, 0.12), 0 1px 2px rgba(0, 0, 0, 0.24) !default;
$base_primary_color: #0D74D7 !default;
$base_primary_hover_color: lighten(#0D74D7, 10%) !default;

body, html {
    width: 100%;
    height: 100%;
    background-color: #f9fafd;
}

body {
    display: flex;
    justify-content: center;
    align-items: center;
    margin: 0;
}

.login {
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 400px;
    box-shadow: $shadow_1;
    background-color: #fff;
    border-radius: 3px;
    box-sizing: border-box;
    padding: 30px;

    &__logo {
        width: 100%;
        height: auto;
    }

    &__divider {
        width: 100%;
        border-bottom: 1px solid #d9d9d9;
        margin: 20px 0;
    }

    &__actions {
        margin-top: 15px;
        text-align: center;
    }

    .input-field__title {
        width: 80px;
    }
}

.link {
    text-decoration: none;
    color: $base_primary_color;
    cursor: pointer;
    transition: color $animation $animation_time;
    font-family: $project_font;

    &__bold {
        font-weight: bold;
    }

    &:hover {
        color: $base_primary_hover_color;
    }
}
</style>
