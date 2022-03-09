import {GetSize, GetType, CheckAttributesCount} from "./helpers"
import moment from "moment";

const getSize = GetSize;
const getType = GetType;
const checkAttributesCount = CheckAttributesCount;
const XRegExp = require('xregexp/lib');

const isValueNull = function (value) {
    return value === null || value === '' || value === [] || value === {};
};

const Rules = function() {
    return {

        // The field under validation must be yes, on, 1, or true. This is useful for validating "Terms of Service" acceptance.
        accepted: (value, attributes, name, fields, nullable) => {
            if(nullable && isValueNull(value)) return true;
            let acceptable = ['yes', 'on', '1', 1, true, 'true'];
            return acceptable.indexOf(value) !== -1;
        },

        // The field under validation must have a valid A or AAAA record according to the dns_get_record PHP function.
        active_url: (value, attributes, name, fields, nullable) => {
        },

        // The field under validation must be a value after a given date.
        // Instead of passing a date, you may specify another field to compare against the date.
        // after:date
        after: (value, attributes, name, fields, nullable) => {
            let field = moment(value);
            let another = moment(fields[attributes]);
            return field.isValid() && another.isValid() && (field > another);
        },

        // The field under validation must be a value after or equal to the given date. For more information, see the after rule.
        // after_or_equal:date
        after_or_equal: (value, attributes, name, fields, nullable) => {
            let field = moment(value);
            let another = moment(attributes);
            return field.isValid() && another.isValid() && (field >= another);
        },

        // The field under validation must be entirely alphabetic characters.
        alpha: (value, attributes, name, fields, nullable) => {
            if(nullable && isValueNull(value)) return true;
            let reg = new XRegExp("^\\pL+$");
            return getType(value) === 'string' && reg.test(value);
        },

        // The field under validation may have alpha-numeric characters, as well as dashes and underscores.
        alpha_dash: (value, attributes, name, fields, nullable) => {
            if(nullable && isValueNull(value)) return true;
            let reg = new XRegExp("^[\\pL\\pN_-]+$");
            return getType(value) === 'string' && reg.test(value);
        },

        // The field under validation must be entirely alpha-numeric characters.
        alpha_num: (value, attributes, name, fields, nullable) => {
            if(nullable && isValueNull(value)) return true;
            let reg = new XRegExp("^[\\pL\\pN]+$");
            return getType(value) === 'string' && reg.test(value);
        },

        // The field under validation must be a PHP array.
        array: (value, attributes, name, fields, nullable) => {
            if(nullable && isValueNull(value)) return true;
            return getType(value) === 'array';
        },

        // The field under validation must be a value preceding the given date. The dates will be passed into the PHP strtotime function. In addition, like the after rule, the name of another field under validation may be supplied as the value of date.
        // before:date
        before: (value, attributes, name, fields, nullable) => {
        },

        // The field under validation must be a value preceding or equal to the given date. The dates will be passed into the PHP strtotime function. In addition, like the after rule, the name of another field under validation may be supplied as the value of date.
        // before_or_equal:date
        before_or_equal: (value, attributes, name, fields, nullable) => {
        },

        // The field under validation must have a size between the given min and max. Strings, numerics, arrays, and files are evaluated in the same fashion as the size rule.
        // between:min,max
        between: (value, attributes, name, fields, nullable) => {
            if(nullable && isValueNull(value)) return true;
            let arr = attributes.split(',');
            let valSize = getSize(value);
            return valSize >= arr[0] && valSize <= arr[1];
        },

        // The field under validation must be able to be cast as a boolean. Accepted input are true,  false, 1, 0, "1", and "0".
        boolean: (value, attributes, name, fields, nullable) => {
            if(nullable && isValueNull(value)) return true;
            let acceptable = [true, false, 0, 1, '0', '1'];
            return acceptable.indexOf(value) !== -1;
        },

        // The field under validation must have a matching field of foo_confirmation. For example, if the field under validation is password, a matching password_confirmation field must be present in the input.
        confirmed: (value, attributes, name, fields, nullable) => {
            if(nullable && isValueNull(value)) return true;
            let confirm = name + '_confirmation';
            return !!fields[confirm] && fields[confirm] === value;
        },

        // The field under validation must be a valid date according to the strtotime PHP function.
        date: (value, attributes, name, fields, nullable) => {
            if(nullable && isValueNull(value)) return true;
            return moment(value).isValid();
        },

        // The field under validation must be equal to the given date. The dates will be passed into the PHP strtotime function.
        // date_equals:date
        date_equals: (value, attributes, name, fields, nullable) => {
        },

        // The field under validation must match the given format. You should use either date or  date_format when validating a field, not both.
        // date_format:format
        date_format: (value, attributes, name, fields, nullable) => {
        },

        // The field under validation must have a different value than field.
        // different:field
        different: (value, attributes, name, fields, nullable) => {
            if(nullable && isValueNull(value)) return true;
            return !fields[attributes] || fields[attributes] !== value;
        },

        // The field under validation must be numeric and must have an exact length of value.
        // digits:value
        digit: (value, attributes, name, fields, nullable) => {
            if(nullable && isValueNull(value)) return true;
            let reg = new XRegExp("[^0-9]");
            let val = String(value);
            return !reg.test(val) && val.length === attributes;
        },

        // The field under validation must have a length between the given min and max.
        // digits_between:min,max
        digits_between: (value, attributes, name, fields, nullable) => {
            if(nullable && isValueNull(value)) return true;
            let reg = new XRegExp("[^0-9]");
            let val = String(value);
            let arr = attributes.split(',');
            return !reg.test(val) && val.length >= arr[0] && val.length <= arr[1];
        },

        // The file under validation must be an image meeting the dimension constraints as specified by the rule's parameters:
        // Available constraints are: min_width, max_width, min_height, max_height, width, height, ratio.
        // 'avatar' => 'dimensions:min_width=100,min_height=200'
        // A ratio constraint should be represented as width divided by height. This can be specified either by a statement like 3/2 or a float like 1.5:
        dimensions: (value, attributes, name, fields, nullable) => {
        },

        // When working with arrays, the field under validation must not have any duplicate values.
        // 'foo.*.id' => 'distinct'
        distinct: (value, attributes, name, fields, nullable) => {
        //     (?)
        },

        /**
         * The field under validation must be formatted as an e-mail address.
         *
         * @param value
         * @param attributes
         * @param name
         * @param fields
         * @param nullable
         * @return {boolean}
         */
        email: (value, attributes, name, fields, nullable) => {
            if(nullable && isValueNull(value)) return true;
            let re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
            return re.test(String(value).toLowerCase());
        },

        // The field under validation must exist on a given database table.
        // exists:table,column
        exists: (value, attributes, name, fields, nullable) => {
            return true;
        },

        // The field under validation must be a successfully uploaded file.
        file: (value, attributes, name, fields, nullable) => {
        },

        // The field under validation must not be empty when it is present.
        filled: (value, attributes, name, fields, nullable) => {
            return Rules().required(value, attributes, name, fields, nullable);
        },

        // The field under validation must be greater than the given field. The two fields must be of the same type. Strings, numerics, arrays, and files are evaluated using the same conventions as the  size rule.
        // gt:field
        gt: (value, attributes, name, fields, nullable) => {
            if(nullable && isValueNull(value)) return true;
            let comparedToValue = fields[attributes];

            if( !comparedToValue  && (getType(value) === 'numeric' && getType(attributes) === 'numeric')){
                return getSize(value) > attributes;
            }

            return getType(value) === getType(comparedToValue) && getSize(value) > getSize(comparedToValue);

        },

        // The field under validation must be greater than or equal to the given field. The two fields must be of the same type. Strings, numerics, arrays, and files are evaluated using the same conventions as the size rule.
        // gte:field
        gte: (value, attributes, name, fields, nullable) => {
            if(nullable && isValueNull(value)) return true;
            let comparedToValue = fields[attributes];

            if( !comparedToValue  && (getType(value) === 'numeric' && getType(attributes) === 'numeric')){
                return getSize(value) >= attributes;
            }

            return getType(value) === getType(comparedToValue) && getSize(value) >= getSize(comparedToValue);
        },

        // The file under validation must be an image (jpeg, png, bmp, gif, or svg)
        image: (value, attributes, name, fields, nullable) => {
        },

        // The field under validation must be included in the given list of values. Since this rule often requires you to implode an array, the Rule::in method may be used to fluently construct the rule:
        // in:foo,bar,...
        in: (value, attributes, name, fields, nullable) => {
            if(nullable && isValueNull(value)) return true;
            let arr = attributes.split(',');

            if(getType(value) === 'array'){
                let ret = true;
                value.map((element) => {
                    if(arr.indexOf(element) === -1){
                        ret = false;
                    }
                });

                return ret;
            }

            return arr.indexOf(value) !== -1;
        },

        // The field under validation must exist in another field's values.
        // in_array:anotherfield
        in_array: (value, attributes, name, fields, nullable) => {
            if(nullable && isValueNull(value)) return true;
            let arr = fields[attributes];

            if(getType(value) === 'array'){
                let ret = true;
                value.map((element) => {
                    if(arr.indexOf(element) === -1){
                        ret = false;
                    }
                });

                return ret;
            }

            return arr.indexOf(value) !== -1;
        },

        /**
         * The field under validation must be an integer.
         *
         * @param value
         * @param attributes
         * @return {boolean}
         */
        integer: (value, attributes, name, fields, nullable) => {
            if(nullable && isValueNull(value)) return true;
            return !isNaN(Number(value)) && Number(value) === Math.round(Number(value));
        },

        // The field under validation must be an IP address.
        ip: (value, attributes, name, fields, nullable) => {
            if(nullable && isValueNull(value)) return true;
            let re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
            return re.test(String(value).toLowerCase());
        },

        // The field under validation must be an IPv4 address.
        ipv4: (value, attributes, name, fields, nullable) => {
            if(nullable && isValueNull(value)) return true;
            let re = /^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/;
            return re.test(String(value).toLowerCase());
        },

        // The field under validation must be an IPv6 address.
        ipv6: (value, attributes, name, fields, nullable) => {
            if(nullable && isValueNull(value)) return true;
            let re = /^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$|^(([a-zA-Z]|[a-zA-Z][a-zA-Z0-9\-]*[a-zA-Z0-9])\.)*([A-Za-z]|[A-Za-z][A-Za-z0-9\-]*[A-Za-z0-9])$|^\s*((([0-9A-Fa-f]{1,4}:){7}([0-9A-Fa-f]{1,4}|:))|(([0-9A-Fa-f]{1,4}:){6}(:[0-9A-Fa-f]{1,4}|((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9A-Fa-f]{1,4}:){5}(((:[0-9A-Fa-f]{1,4}){1,2})|:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9A-Fa-f]{1,4}:){4}(((:[0-9A-Fa-f]{1,4}){1,3})|((:[0-9A-Fa-f]{1,4})?:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){3}(((:[0-9A-Fa-f]{1,4}){1,4})|((:[0-9A-Fa-f]{1,4}){0,2}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){2}(((:[0-9A-Fa-f]{1,4}){1,5})|((:[0-9A-Fa-f]{1,4}){0,3}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){1}(((:[0-9A-Fa-f]{1,4}){1,6})|((:[0-9A-Fa-f]{1,4}){0,4}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(:(((:[0-9A-Fa-f]{1,4}){1,7})|((:[0-9A-Fa-f]{1,4}){0,5}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:)))(%.+)?\s*$/;
            return re.test(String(value).toLowerCase());
        },

        // The field under validation must be a valid JSON string.
        json: (value, attributes, name, fields, nullable) => {
            if(nullable && isValueNull(value)) return true;
            try {
                let val = JSON.parse(value);
                return true;
            } catch (e) {
                return false;
            }
        },

        // The field under validation must be less than the given field. The two fields must be of the same type. Strings, numerics, arrays, and files are evaluated using the same conventions as the size rule.
        // lt:field
        lt: (value, attributes, name, fields, nullable) => {
            if(nullable && isValueNull(value)) return true;
            let comparedToValue = fields[attributes];

            if( !comparedToValue  && (getType(value) === 'numeric' && getType(attributes) === 'numeric')){
                return getSize(value) < attributes;
            }

            return getType(value) === getType(comparedToValue) && getSize(value) < getSize(comparedToValue);
        },

        // The field under validation must be less than or equal to the given field. The two fields must be of the same type. Strings, numerics, arrays, and files are evaluated using the same conventions as the size rule.
        // lte:field
        lte: (value, attributes, name, fields, nullable) => {
            if(nullable && isValueNull(value)) return true;
            let comparedToValue = fields[attributes];

            if( !comparedToValue  && (getType(value) === 'numeric' && getType(attributes) === 'numeric')){
                return getSize(value) <= attributes;
            }

            return getType(value) === getType(comparedToValue) && getSize(value) <= getSize(comparedToValue);
        },

        /**
         * The field under validation must be less than or equal to a maximum value. Strings, numerics, arrays, and files are evaluated in the same fashion as the size rule.
         * Usage: 'max:value'
         *
         * @param value
         * @param attributes
         * @param name
         * @param fields
         * @param nullable
         * @return {boolean}
         */
        max: (value, attributes, name, fields, nullable) => {
            if(nullable && isValueNull(value)) return true;
            return getSize(value) <= attributes;
        },

        // The file under validation must match one of the given MIME types:
        // 'video' => 'mimetypes:video/avi,video/mpeg,video/quicktime'
        // To determine the MIME type of the uploaded file, the file's contents will be read and the framework will attempt to guess the MIME type, which may be different from the client provided MIME type.
        // mimetypes:text/plain,...
        mimetypes: (value, attributes, name, fields, nullable) => {
        },

        // The file under validation must have a MIME type corresponding to one of the listed extensions.
        // Even though you only need to specify the extensions, this rule actually validates against the MIME type of the file by reading the file's contents and guessing its MIME type.
        // A full listing of MIME types and their corresponding extensions may be found at the following location: https://svn.apache.org/repos/asf/httpd/httpd/trunk/docs/conf/mime.types
        // 'photo' => 'mimes:jpeg,bmp,png'
        // mimes:foo,bar,...
        mimes: (value, attributes, name, fields, nullable) => {
        },

        /**
         * The field under validation must have a minimum value. Strings, numerics, arrays, and files are evaluated in the same fashion as the size rule.
         * Usage: 'min:value'
         *
         * @param value
         * @param attributes
         * @param name
         * @param fields
         * @param nullable
         * @return {boolean}
         */
        min: (value, attributes, name, fields, nullable) => {
            if(nullable && isValueNull(value)) return true;
            return getSize(value) >= attributes;
        },

        // The field under validation must not be included in the given list of values. The Rule::notIn method may be used to fluently construct the rule:
        // not_in:foo,bar,...
        not_in: (value, attributes, name, fields, nullable) => {
            if(nullable && isValueNull(value)) return true;
            return !Rules().in(value, attributes, name, fields, nullable);
        },

        // The field under validation must not match the given regular expression.
        // Note: When using the regex / not_regex patterns, it may be necessary to specify rules in an array instead of using pipe delimiters, especially if the regular expression contains a pipe character.
        // not_regex:pattern
        not_regex: (value, attributes, name, fields, nullable) => {
        },

        // The field under validation may be null. This is particularly useful when validating primitive such as strings and integers that can contain null values.
        nullable: (value, attributes, name, fields, nullable) => {
        },

        // The field under validation must be numeric.
        numeric: (value, attributes, name, fields, nullable) => {
        },

        // The field under validation must be present in the input data but can be empty.
        present: (value, attributes, name, fields, nullable) => {
        },

        // The field under validation must match the given regular expression.
        // Note: When using the regex / not_regex patterns, it may be necessary to specify rules in an array instead of using pipe delimiters, especially if the regular expression contains a pipe character.
        // regex:pattern
        regex: (value, attributes, name, fields, nullable) => {
        },

        /**
         * The field under validation must be present in the input data and not empty. A field is considered "empty" if one of the following conditions are true:
         * The value is null.
         * The value is an empty string.
         * The value is an empty array or empty Countable object.
         * The value is an uploaded file with no path.
         *
         * @param value
         * @param attributes
         * @param name
         * @param fields
         * @param nullable
         * @return {boolean}
         */
        required: (value, attributes, name, fields, nullable) => {
            if(nullable && isValueNull(value)) return true;
            if (value == null) return false;
            if (typeof value === 'string' && value.trim() === '') return false;
            if (typeof value === 'object' && value.length === 0) return false;
            // } elseif ($value instanceof File) {
            //     return (string) $value->getPath() !== '';
            // }

            return true;
        },

        // The field under validation must be present and not empty if the another field is equal to any value.
        // required_if:another_field,value,...
        required_if: (value, attributes, name, fields, nullable) => {
            const attrs = attributes.split(',');
            const field = attrs.splice(0, 1);

            return attrs.indexOf(String(fields[field])) === -1 || !isValueNull(value);
        },

        // The field under validation must be present and not empty unless the another field is equal to any value.
        // required_unless:another_field,value,...
        required_unless: (value, attributes, name, fields, nullable) => {
        },

        // The field under validation must be present and not empty only if any of the other specified fields are present.
        // required_with:foo,bar,...
        required_with: (value, attributes, name, fields, nullable) => {
        },

        // The field under validation must be present and not empty only if all of the other specified fields are present.
        // required_with_all:foo,bar,...
        required_with_all: (value, attributes, name, fields, nullable) => {
        },

        // The field under validation must be present and not empty only when any of the other specified fields are not present.
        // required_without:foo,bar,...
        required_without: (value, attributes, name, fields, nullable) => {
        },

        // The field under validation must be present and not empty only when all of the other specified fields are not present.
        // required_without_all:foo,bar,...
        required_without_all: (value, attributes, name, fields, nullable) => {
        },

        // The given field must match the field under validation.
        // same:field
        same: (value, attributes, name, fields, nullable) => {
            if(nullable && isValueNull(value)) return true;
            checkAttributesCount(attributes, 1);
            return value === fields[attributes];
        },

        // The field under validation must have a size matching the given value. For string data, value corresponds to the number of characters. For numeric data, value corresponds to a given integer value. For an array, size corresponds to the count of the array. For files, size corresponds to the file size in kilobytes.
        // size:value
        size: (value, attributes, name, fields, nullable) => {
        },

        // The field under validation must be a string. If you would like to allow the field to also be null, you should assign the nullable rule to the field.
        string: (value, attributes, name, fields, nullable) => {
        },

        // The field under validation must be a valid timezone identifier according to the  timezone_identifiers_list PHP function.
        timezone: (value, attributes, name, fields, nullable) => {
        },

        // The field under validation must be unique in a given database table. If the column option is not specified, the field name will be used.
        // unique:table,column,except,idColumn
        unique: (value, attributes, name, fields, nullable) => {
            return true;
        },

        // The field under validation must be a valid URL.
        url: (value, attributes, name, fields, nullable) => {
        },

    };
};


export default Rules;
