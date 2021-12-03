import {GetType} from "./helpers";
import localization from "./localization";

const getType = GetType;

// TODO: Date Equals = date_equals:date

/**
 * Association for rules and message formatter function
 */
const messageFormatter = {
    singleAttributeMessage: [
        'accepted', 'active_url', 'alpha', 'alpha_dash', 'alpha_num', 'array',
        'boolean',
        'confirmed',
        'date', 'dimensions', 'distinct',
        'email', 'exists',
        'file', 'filled',
        'image', 'in', 'integer', 'ip', 'ipv4', 'ipv6',
        'json',
        'not_in', 'not_regex', 'numeric',
        'present',
        'regex', 'required',
        'string',
        'timezone',
        'unique', 'uploaded', 'url',
    ],
    minMaxAttributeMessage: [
        'digits_between',
    ],
    minMaxArrayAttributeMessage: [
        'between',
    ],
    maxArrayAttributeMessage: [
        'max',
    ],
    minArrayAttributeMessage: [
        'min',
    ],
    dateAttributeMessage: [
        'after', 'after_or_equal', 'before', 'before_or_equal',
    ],
    formatAttributeMessage: [
        'date_format'
    ],
    sizeAttributeMessage: [
        'size'
    ],
    digitsAttributeMessage: [
        'digits',
    ],
    otherAttributeMessage: [
        'different', 'same', 'in_array',
    ],
    valueAttributeMessage: [
        'gt', 'gte', 'lt', 'lte',
    ],
    valuesAttributeMessage: [
        'required_with', 'required_with_all',
        'required_without', 'required_without_all',
    ],
    mimesAttributeMessage: [
        'mimes', 'mimetypes',
    ],
    otherValueAttributeMessage: [
        'required_if',
    ],
    otherValuesAttributeMessage: [
        'required_unless',
    ],
};

/**
 * Replace 'search' with 'replace' in message.
 *
 * @param message
 * @param search
 * @param replace
 * @return {*}
 */
const replacePart = function (message, search, replace) {
    return message.replace(search, replace);
};

const messageFormatters = {
    singleAttributeMessage: (message) => {
        return message;
    },

    minMaxAttributeMessage: (message, name, failed_rule, validation_rule) => {
        let attributes = validation_rule[failed_rule].split(',');
        message = replacePart(message, ':min', attributes[0]);
        message = replacePart(message, ':max', attributes[1]);
        return message;
    },

    minArrayAttributeMessage: (message, name, failed_rule, validation_rule) => {
        return replacePart(message, ':min', validation_rule[failed_rule]);
    },

    minMaxArrayAttributeMessage: (message, name, failed_rule, validation_rule) => {
        let attributes = validation_rule[failed_rule].split(',');
        message = replacePart(message, ':min', attributes[0]);
        message = replacePart(message, ':max', attributes[1]);
        return message;
    },

    maxArrayAttributeMessage: (message, name, failed_rule, validation_rule) => {
        return replacePart(message, ':max', validation_rule[failed_rule]);
    },

    dateAttributeMessage: (message, name, failed_rule, validation_rule, fields) => {
        let attributes = validation_rule[failed_rule];
        if (!!fields[attributes]) {
            attributes = '"' + [fields[attributes]] + '"';
        }
        return replacePart(message, ':date', attributes);
    },

    formatAttributeMessage: (message, name, failed_rule, validation_rule) => {
        return replacePart(message, ':format', validation_rule[failed_rule]);
    },

    sizeAttributeMessage: (message, name, failed_rule, validation_rule) => {
        return replacePart(message, ':size', validation_rule[failed_rule]);
    },

    digitsAttributeMessage: (message, name, failed_rule, validation_rule) => {
        return replacePart(message, ':digits', validation_rule[failed_rule]);
    },

    otherAttributeMessage: (message, name, failed_rule, validation_rule, titles) => {
        let attributes = validation_rule[failed_rule];
        let other = titles[attributes];

        return replacePart(message, ':other', other);
    },

    valueAttributeMessage: (message, name, failed_rule, validation_rule, titles) => {
        let attributes = validation_rule[failed_rule];
        let other = '"' + titles[attributes] + '"';

        return replacePart(message, ':value', other);
    },

    valuesAttributeMessage: (message, name, failed_rule, validation_rule, titles) => {
        let attributes = validation_rule[failed_rule].split(',');
        let others = [];
        attributes.map((name) => {
            others.push('"' + titles[name] + '"');
        });

        return replacePart(message, ':values', others.join(', '));
    },

    otherValueAttributeMessage: (message, name, failed_rule, validation_rule, titles, values) => {
        const attributes = validation_rule[failed_rule].split(',');
        let other = attributes.splice(0, 1);
        const val = values[other];
        other = titles[other];
        message = replacePart(message, ':other', other);
        message = replacePart(message, ':value', val);

        return message;
    },

    otherValuesAttributeMessage: (message, name, failed_rule, validation_rule, titles) => {
        let attributes = validation_rule[failed_rule].split(',');
        let other = titles[attributes[0]];
        message = replacePart(message, ':other', other);
        message = replacePart(message, ':values', attributes.splice(0, 1).join(', '));

        return message;
    },

    mimesAttributeMessage: (message, name, failed_rule, validation_rule) => {
        return replacePart(message, ':values', validation_rule[failed_rule].split(',').join(', '));
    },
};

/**
 * Make whole validation error message for given rule and field.
 */
const getMessage = function (name, value, failed_rule, validation_rule, titles, values) {
    let message = localization[failed_rule];

    if (typeof message === 'object') {
        let type = getType(value);
        if (!message[type]) return null;
        message = message[type];
    }

    if (!message || typeof message !== 'string') return null;

    message = message.replace(':attribute', '"' + titles[name] + '"');

    let formattedMessage = null;

    let formatters = Object.keys(messageFormatter);

    for (let i = 0; i < formatters.length; i++) {
        if (messageFormatter[formatters[i]].indexOf(failed_rule) !== -1) {
            formattedMessage = messageFormatters[formatters[i]](message, name, failed_rule, validation_rule, titles, values);
        }
    }

    return formattedMessage;
};

export {getMessage}
