import Rules from "./rules"

const rules = Rules();

/**
 * Parse validation rules from string to rule object.
 *
 * @param rules
 * @return {{}}
 */
const parseRules = function (rules) {
    if (!rules) return {};

    let _rule_set = rules.split('|');
    if (_rule_set.length === 0) return {};

    let _rules = {};
    _rule_set.map((rule) => {
        let attributes = rule.split(':');
        _rules[attributes[0]] = attributes[1];
    });

    return _rules;
};

const validate = function (field_name, value, validation_rules, fields) {
    if (!field_name || field_name.length === 0 || Object.keys(validation_rules).length === 0) {
        return true;
    }

    let failedRules = [];

    let rule_set = Object.keys(validation_rules);

    let bail = Object.keys(validation_rules).indexOf('bail');
    let nullable = Object.keys(validation_rules).indexOf('nullable');

    if (bail !== -1) {
        rule_set.splice(bail, 1);
    }

    if (nullable !== -1) {
        rule_set.splice(nullable, 1);
    }

    for (let i = 0; i < rule_set.length; i++) {
        let attributes = validation_rules[rule_set[i]];
        if (typeof rules[rule_set[i]] === 'function') {
            let ruleCheck = null;
            try {
                ruleCheck = rules[rule_set[i]](value, attributes, field_name, fields, nullable !== -1);
            } catch (e) {
                console.error('Failed to check rule "' + rule_set[i] + '" on "' + field_name + '" field.', e);
            }
            if(ruleCheck === false) {
                failedRules.push(rule_set[i]);
                if (bail !== -1) {
                    return failedRules;
                }
            }
        }
    }

    return failedRules;
};

export {parseRules, validate};
