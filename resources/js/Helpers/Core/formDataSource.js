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
        validation_rules: {},

        payload: null,

        valid: {},
        validation_errors: {},

        loading: false,

        load() {
            this.loading = true;

            axios.post(this.dataSourceUrl, this.options)
                .then(response => {
                    this.values = response.data.data.values;
                    this.payload = typeof response.data.payload !== "undefined" ? response.data.payload : null;
                    this.originals = clone(this.values);
                    Object.keys(this.values).map(key => {
                        this.validation_rules[key] = parseRules(response.data.data.rules[key]);
                    });
                    this.validateAll();
                })
                .catch(error => {
                    console.log(error);
                })
                .finally(() => {
                    this.loading = false;
                });
        },

        validateAll() {
            Object.keys(this.values).map(key => {
                this.validate(key, this.values[key]);
            });
        },

        validate(name, value) {
            if (Object.keys(this.validation_rules[name]).length === 0) {
                this.validation_errors[name] = [];
                this.valid[name] = true;
                // this.updateErrorsCount(field_name, initialValid, true);
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
                let names = {};
                Object.keys(this.values).map(field => {
                    names[field] = 'caption';
                });
                this.validation_errors[name].push(getMessage(name, value, failed_rule, this.validation_rules[name], names, this.values));
            });

            this.valid[name] = false;

            return false;
        },

        // hasErrors() {
        //     return Object.values(this.valid).some(val => !val);
        // },
        //
        // sectionHasErrors(section) {
        //     return Object.keys(this.fields).some(field => {
        //         let _section = !!this.fields[field]['section'] ? this.fields[field]['section'] : null;
        //         if (section === _section) {
        //             return this.valid[field] === false;
        //         }
        //         return false;
        //     });
        // },
        //
        // sectionHasModified(section) {
        //     return Object.keys(this.fields).some(field => {
        //         let _section = !!this.fields[field]['section'] ? this.fields[field]['section'] : null;
        //         if (section === _section) {
        //             return this.isFieldModified(field);
        //         }
        //         return false;
        //     });
        // },
        //
        // reset() {
        //     let needsReload = false;
        //
        //     Object.keys(this.fields).map(field => {
        //
        //         if (this.isFieldModified(field)) {
        //             try {
        //                 const json = JSON.stringify(this.fields[field]['value']);
        //                 const parsed = JSON.parse(json);
        //                 this.set(field, parsed);
        //                 needsReload = needsReload || !!this.fields[field]['needs_reload'];
        //             } catch (e) {
        //
        //             }
        //         }
        //     });
        //
        //     return needsReload;
        // },
    }

    form.dataSourceUrl = dataSourceUrl;
    form.dataTargetUrl = dataTargetUrl;
    form.options = options;

    return form;
};

export default formDataSource;
