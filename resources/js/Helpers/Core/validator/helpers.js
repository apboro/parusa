/**
 * Get the size of an attribute.
 * This method will determine if the attribute is a number, string, or file and
 * return the proper size accordingly. If it is a number, then number itself
 * is the size. If it is a file, we take kilobytes, and for a string the
 * entire length of the string will be considered the attribute size.
 *
 * @param value
 * @return Number
 */
const GetSize = function (value)
{
    if (value === null) return 0;
    if (typeof value === 'number') return value;
    if (typeof value === 'object') return value.length;
    if (typeof value === 'string') return value.length;
    // TODO: check for files
    return 0;
};

/**
 * Get type of variable.
 * This method will determine if the attribute is a number, string, array or file and
 * return the proper string value accordingly laravel validator.
 *
 * @param value
 * @return String|Null
 */
const GetType = function (value)
{
    if (typeof value === 'number') return 'numeric';
    if (typeof value === 'object') return 'array';
    if (typeof value === 'string') return 'string';
    // TODO: check for files
    return null;
};

/**
 * Check attributes for proper parameters count.
 *
 * @param attributes
 * @param count
 */
const CheckAttributesCount = function (attributes, count) {
  if(count > 0 && (!attributes || attributes.split(',').length < count)) {
      throw "Not enough parameters in validation rule.";
  }
};

export {GetSize, GetType, CheckAttributesCount};

