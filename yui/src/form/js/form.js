/**
 * JavaScript for form editing grade conditions.
 *
 * @module moodle-availability_coursepayment-form
 */
M.availability_coursepayment = M.availability_coursepayment || {};
/**
 * @class M.availability_coursepayment.form
 * @extends M.core_availability.plugin
 */
M.availability_coursepayment.form = Y.Object(M.core_availability.plugin);
/**
 * Initialises this plugin.
 *
 * @method initInner
 * @param {Array} param Array of objects
 */
M.availability_coursepayment.form.initInner = function (params) {
    console.log('M.availability_coursepayment');
    console.log(params);
};

M.availability_coursepayment.form.getNode = function (json) {
    // This function does the main work. It gets called after the user
    // chooses to add an availability restriction of this type. You have
    // to return a YUI node representing the HTML for the plugin controls.
    console.log(json);

    var strings = M.str.availability_coursepayment;
    var html = '<label><b>' + strings.title + '</b></label><br/>' +
        '<label for="cost">' + strings.cost + ' </label> ' +
        '<input type="text" placeholder="0.00" name="cost" title="' + strings.cost + '"/><br/>' +
        '<label for="currency">' + strings.currency + ' </label>' +
        '<select name="currency">' +
        '<option value="EUR" selected="selected">Euro</option>' +
        '<option value="USD">US Dollar</option>' +
        '</select><br/>' +
        '<label for="vat">' + strings.vat + ' </label><br/>' +
        '<select name="vat">';
    for (var i = 0; i < 50; i++) {
        var selected = (i === 21) ? 'selected="selected"' : '';
        html += '<option ' + selected + 'value="' + i + '">' + i + '</option>';
    }

    html += '</select>';

    var node = Y.Node.create('<span>' + html + '</span>');

    // Set initial values based on the value from the JSON data in Moodle
    // database. This will have values undefined if creating a new one.

    if (json.cost !== undefined) {
        node.one('input[name=cost]').set('value', json.cost);
    }

    if (json.currency !== undefined) {
        node.one('[name=currency]').set('value', json.currency);
    }

    if (json.vat !== undefined) {
        node.one('[name=vat]').set('value', json.vat);
    }

    // Add event handlers (first time only). You can do this any way you
    // like, but this pattern is used by the existing code.
    if (!M.availability_coursepayment.form.addedEvents) {
        M.availability_coursepayment.form.addedEvents = true;
        var root = Y.one('#fitem_id_availabilityconditionsjson');
        root.delegate('change', function () {
            // The key point is this update call. This call will update
            // the JSON data in the hidden field in the form, so that it
            // includes the new value of the checkbox.
            M.core_availability.form.update();
        }, '.availability_coursepayment input, .availability_coursepayment select');
    }

    return node;
};

M.availability_coursepayment.form.fillValue = function (value, node) {
    // This function gets passed the node (from above) and a value
    // object. Within that object, it must set up the correct values
    // to use within the JSON data in the form. Should be compatible
    // with the structure used in the __construct and save functions
    // within condition.php.

    value.cost = this.getValue('cost', node);
    value.currency = this.getValue('currency', node);
    value.vat = this.getValue('vat', node);
};

/**
 * Gets the numeric value of an input field. Supports decimal points (using
 * dot or comma).
 *
 * @method getValue
 * @return {Number|String} Value of field as number or string if not valid
 */
M.availability_coursepayment.form.getValue = function (field, node) {
    var value;
    // Get field value.
    var node = node.one('[name=' + field + ']');
    if (node) {
        value = node.get('value');
        // Make , = .
        value = value.replace(/,/g, '.');
    }

    // If it is not a valid positive number, return false.
    var reg = new RegExp('^\\d.+$');
    if (reg.test(value)) {
        if(field === 'vat'){
            console.log('vat:' + value);
            return parseInt(value);
        }

        return parseFloat(value).toFixed(2);
    }

    console.log('String:' + value);
    return value;
};

M.availability_coursepayment.form.fillErrors = function (errors, node) {
    var value = {};
    var reg = new RegExp('^[\\d.]+$');

    this.fillValue(value, node);

    // Check numeric values.
    if ((value.cost !== undefined)) {

        if (!reg.test(value.cost)) {
            console.log('cost - availability_coursepayment:error_invalidnumber');
            errors.push('availability_coursepayment:error_invalidnumber');
        }
    }
    // Check vat.
    if ((value.vat !== undefined)) {
        if (!reg.test(value.vat)) {
            console.log('vat - availability_coursepayment:error_invalidnumber ' + value.vat);
            errors.push('availability_coursepayment:error_invalidnumber');
        }
    }
};