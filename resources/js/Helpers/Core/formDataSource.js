import {parseRules, validate} from "./validator/validator"
import {getMessage} from "./validator/messages";
import clone from "../Lib/clone";
import empty from "../Lib/empty";

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
        onLoad: null,
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
                    if (typeof this.onLoad === "function") {
                        this.onLoad(this.values, this.payload);
                    }
                })
                .catch(error => {
                    if (this.toaster !== null) {
                        this.toaster.error(error.response.data.message, 5000);
                    } else {
                        console.log(error.response.data.message);
                    }
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
                    if (this.toaster !== null) {
                        this.toaster.success(response.data.message, 3000);
                    } else {
                        console.log(response.data.message);
                    }
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
                    }
                    if (this.toaster !== null) {
                        this.toaster.error(error.response.data.message, 5000);
                    } else {
                        console.log(error.response.data.message);
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
            if (empty(this.validation_rules)) {
                return true;
            }
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
    }

    form.dataSourceUrl = dataSourceUrl;
    form.dataTargetUrl = dataTargetUrl;
    form.options = options;

    return form;
};

export default formDataSource;
