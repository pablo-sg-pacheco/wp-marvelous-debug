/**
 * Shared utilities.
 */

/**
 * Uppercase a string.
 *
 * @param {string} message
 */
exports.upper = ( message ) => message.toUpperCase();

/**
 * Test if is type string.
 *
 * @param {string} message
 */
exports.isString = ( message ) => 'string' === typeof message;
