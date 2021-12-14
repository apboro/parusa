import {parseRules, validate} from "./validator/validator"
import {getMessage} from "./validator/messages";
import clone from "../Lib/clone";

const formDataSource = function (dataSourceUrl, dataTargetUrl, options) {
    let form = {
        dataSourceUrl: null,
        dataTargetUrl: null,
        options: {},

        originals: {},
        values: {},
        titles: {},
        validation_rules: {},

        payload: {},

        valid: {},
        valid_all: false,
        validation_errors: {},

        loaded: false,
        loading: false,
        saving: false,

        toaster: null,
        afterSave: null,

        load() {
            this.loading = true;

            axios.post(this.dataSourceUrl, this.options)
                .then(response => {
                    this.values = response.data.values;
                    this.originals = clone(this.values);
                    this.titles = response.data.titles;
                    Object.keys(this.values).map(key => {
                        this.validation_rules[key] = parseRules(response.data.rules[key]);
                    });
                    this.payload = typeof response.data.payload !== "undefined" ? response.data.payload : {};
                    this.loaded = true;
                })
                .catch(error => {
                    console.log(error);
                })
                .finally(() => {
                    this.loading = false;
                });
        },

        save() {
            this.saving = true;

            let options = clone(this.options);
            options['data'] = this.values;

            axios.post(this.dataTargetUrl, options)
                .then(response => {
                    this.toast(response.data.message, 5000, 'success');
                    this.originals = clone(this.values);
                    this.payload = typeof response.data.payload !== "undefined" ? response.data.payload : {};
                    if (typeof this.afterSave === "function") {
                        this.afterSave(response.data.payload);
                    }
                })
                .catch(error => {
                    if (error.response.status === 422) {
                        if (typeof error.response.data.errors !== "undefined") {
                            this.validation_errors = error.response.data.errors;
                            Object.keys(this.validation_errors).map(key => {
                                this.valid[key] = false;
                            });
                        }
                        this.toast('Не все поля корректно заполнены', 5000, 'error');
                    } else {
                        let message;
                        if (typeof error.response.data.status !== "undefined") {
                            message = error.response.data.status;
                        } else if (typeof error.response.data.message !== "undefined") {
                            message = error.response.data.message;
                        } else {
                            message = error.response.status;
                        }
                        this.toast('Ошибка: ' + message, 0, 'error');
                    }
                })
                .finally(() => {
                    this.saving = false;
                });
        },

        validateAll() {
            this.valid_all = true;
            Object.keys(this.values).map(key => {
                const valid = this.validate(key, this.values[key]);
                this.valid_all = this.valid_all && valid;
            });
            return this.valid_all;
        },

        validate(name, value) {
            if (Object.keys(this.validation_rules[name]).length === 0) {
                this.validation_errors[name] = [];
                this.valid[name] = true;
                return true;
            }

            let failed = validate(name, value, this.validation_rules[name], this.values);

            if (failed.length === 0) {
                this.validation_errors[name] = [];
                this.valid[name] = true;
                // this.updateErrorsCount(field_name, initialValid, true);
                return true;
            }

            this.validation_errors[name] = [];
            failed.map((failed_rule) => {
                this.validation_errors[name].push(getMessage(name, value, failed_rule, this.validation_rules[name], this.titles, this.values));
            });

            this.valid[name] = false;

            return false;
        },

        toast(message, delay = null, type = null) {
            if (this.toaster !== null) {
                this.toaster.show(message, delay, type);
            } else {
                console.log(type + ' ' + message);
            }
        },
        // hasErrors() {
        //     return Object.values(this.valid).some(val => !val);
        // },
    }

    form.dataSourceUrl = dataSourceUrl;
    form.dataTargetUrl = dataTargetUrl;
    form.options = options;

    return form;
};

export default formDataSource;
