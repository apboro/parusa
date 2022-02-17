import {parseRules, validate} from "./Validator/validator"
import {getMessage} from "./Validator/messages";
import clone from "@/Core/Helpers/Clone";
import empty from "@/Core/Helpers/Empty";

const form = function (load_url, save_url, options = {}) {
    let form = {

        /** Url to load form data */
        load_url: null,
        /** Url to save form data */
        save_url: null,
        /** Default options to pass to request */
        options: {},

        /** Validate field on update */
        validate_on_update: false,

        /** form payload */
        payload: {},

        values: {},
        originals: {},
        titles: {},
        rules: {},

        valid: {},
        errors: {},

        is_loaded: false,
        is_loading: false,
        is_saving: false,
        is_saved: false,

        toaster: null,

        /** Callbacks */
        loaded_callback: null,
        load_failed_callback: null,
        saved_callback: null,
        save_failed_callback: null,

        /**
         * Load form data.
         *
         * @param options Options to pass to load request. Overrides form default options if not null.
         */
        load(options = null) {
            return new Promise((resolve, reject) => {
                if (this.load_url === null || this.load_url === '') {
                    this.is_loaded = true;
                    if (typeof this.loaded_callback === "function") {
                        this.loaded_callback(this.values, this.payload);
                    }
                    resolve(this.values);
                    return;
                }

                this.is_loaded = false;
                this.is_loading = true;
                this.is_saving = false;

                axios.post(this.load_url, options !== null ? options : this.options)
                    .then(response => {
                        this.values = response.data.values;
                        this.originals = clone(this.values);
                        this.titles = response.data.titles;
                        this.rules = {};
                        Object.keys(response.data.rules).map(key => {
                            this.rules[key] = parseRules(response.data.rules[key]);
                        });
                        this.payload = !empty(response.data.payload) ? response.data.payload : {};

                        this.is_loaded = true;

                        if (typeof this.loaded_callback === "function") {
                            this.loaded_callback(this.values, this.payload);
                        }

                        resolve(this.values);
                    })
                    .catch(error => {
                        this.notify(error.response.data.message, 0, 'error');

                        if (typeof this.load_failed_callback === "function") {
                            this.load_failed_callback(error.response.status, error.response.data);
                        }

                        reject(error.response.data);
                    })
                    .finally(() => {
                        this.is_loading = false;
                    });
            });
        },

        /**
         * Save form data.
         *
         * @param options Options to pass to load request. Overrides form default options if not null.
         */
        save(options = null) {
            return new Promise((resolve, reject) => {
                if (this.is_loading || !this.is_loaded) {
                    this.notify('Form is not loaded or in loading process.', 0, 'error');
                    return;
                }
                if (this.save_url === null) {
                    this.notify('Save url is not defined.', 0, 'error');
                    return;
                }
                this.is_saving = false;

                let _options = clone(options !== null ? options : this.options);
                _options['data'] = this.values;

                axios.post(this.save_url, _options)
                    .then(response => {
                        this.notify(response.data.message, 5000, 'success');
                        this.originals = clone(this.values);
                        if (!empty(response.data.payload)) {
                            this.payload = response.data.payload;
                        }
                        if (typeof this.saved_callback === "function") {
                            this.saved_callback(this.values, this.payload);
                        }
                        resolve(this.values);
                    })
                    .catch(error => {
                        if (error.response.status === 422) {
                            this.notify(error.response.data.message, 5000, 'error');
                            if (typeof error.response.data.errors !== "undefined") {
                                this.errors = error.response.data.errors;
                                Object.keys(this.errors).map(key => {
                                    this.valid[key] = false;
                                });
                                this.is_valid = false;
                            }
                        } else {
                            this.notify(error.response.data.message, 0, 'error');
                            if (typeof this.save_failed_callback === "function") {
                                this.save_failed_callback(error.response.status, error.response.data);
                            }
                        }
                        reject(error.response.data);
                    })
                    .finally(() => {
                        this.is_saved = false;
                    });
            });
        },

        /**
         * Show notification to user.
         *
         * @param message
         * @param delay
         * @param type
         */
        notify(message, delay, type) {
            if (this.toaster !== null) {
                this.toaster.show(message, delay, type);
            } else {
                if (type === 'error') {
                    console.error(message);
                } else {
                    console.log(message);
                }
            }
        },

        /**
         * Set field attributes.
         *
         * @param name
         * @param value
         * @param rules
         * @param title
         * @param initial
         */
        set(name, value, rules = undefined, title = null, initial = false) {
            this.values[name] = value;
            if (rules !== undefined) {
                if (empty(rules)) {
                    delete this.rules[name];
                } else {
                    this.rules[name] = parseRules(rules);
                }
            }
            if (title !== null || typeof this.titles[name] === "undefined") {
                this.titles[name] = title;
            }
            if (initial) {
                this.originals[name] = value;
            }
        },

        /**
         * Update field value.
         *
         * @param name
         * @param value
         * @param force Force validation
         */
        update(name, value, force = false) {
            this.values[name] = value;
            if (force || this.validate_on_update) {
                this.validate(name);
            } else {
                this.errors[name] = [];
                this.valid[name] = true;
            }
        },

        /**
         * Validate all form or single field.
         *
         * @param name
         * @returns {boolean}
         */
        validate(name = null) {
            if (name === null) {
                let all = true;
                Object.keys(this.rules).map(key => {
                    all = this.validate(key) && all;
                });
                return all;
            }
            if (empty(this.rules) || Object.keys(this.rules[name]).length === 0) {
                this.errors[name] = [];
                this.valid[name] = true;
                return true;
            }
            let failed = validate(name, this.values[name], this.rules[name], this.values);

            if (failed.length === 0) {
                this.errors[name] = [];
                this.valid[name] = true;
                return true;
            }

            this.errors[name] = [];
            failed.map((failed_rule) => {
                this.errors[name].push(getMessage(name, this.values[name], failed_rule, this.rules[name], this.titles, this.values));
            });

            this.valid[name] = false;

            return false;
        },

        reset() {
            this.payload = {};
            this.values = {};
            this.originals = {};
            this.titles = {};
            this.rules = {};
            this.valid = {};
            this.errors = {};
            this.is_loaded = false;
            this.is_loading = false;
            this.is_saving = false;
            this.is_saved = false;
        },

        unset(name) {
            delete this.values[name];
            delete this.originals[name];
            delete this.titles[name];
            delete this.rules[name];
            delete this.valid[name];
            delete this.errors[name];
        },
    }

    form.load_url = load_url;
    form.save_url = save_url;
    form.options = options;

    return form;
};

export default form;
