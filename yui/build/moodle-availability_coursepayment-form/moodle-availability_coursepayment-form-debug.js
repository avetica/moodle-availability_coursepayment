YUI.add('moodle-availability_coursepayment-form', function (Y, NAME) {

/**
 * JavaScript for form editing grade conditions.
 *
 * @module moodle-availability_coursepayment-form
 */
/* eslint no-console: "error" */
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
M.availability_coursepayment.form.initInner = function(params) {
    // eslint-disable-next-line no-console
    console.log('M.availability_coursepayment', params);
};

M.availability_coursepayment.form.getNode = function(json) {
    // This function does the main work. It gets called after the user
    // chooses to add an availability restriction of this type. You have
    // to return a YUI node representing the HTML for the plugin controls.
    // eslint-disable-next-line no-console
    console.log('JSON', json);

    var strings = M.str.availability_coursepayment;
    var html = '<b>' + strings.title + '</b><br/>' +
        ' <div class="form-group row">' +
        '   <label for="cost" class="col-sm-6 col-form-label">' + strings.cost + ' </label> ' +
        '   <div class="col-sm-6">' +
        '       <input type="number" step="any" class="form-control" placeholder="0.00" name="cost" title="' + strings.cost + '"' +
        '            value="10"/>' +
        '   </div>' +
        '</div>' +
        ' <div class="form-group row">' +
        '   <label for="currency" class="col-sm-6 col-form-label">' + strings.currency + ' </label>' +
        '   <div class="col-sm-6">' +
        '       <select name="currency" class="form-control" >' +
        '           <option value="EUR" selected="selected">Euro</option>' +
        '       </select>' +
        '   </div>' +
        '</div>' +
        '<div class="form-group row">' +
        '   <label for="vat" class="col-sm-6 col-form-label">' + strings.vat + ' </label>' +
        '   <div class="col-sm-6">' +
        '       <select name="vat" class="form-control" >';

    for (var i = 0; i < 50; i++) {
        var selected = (i === 21) ? 'selected="selected"' : '';
        html += '   <option ' + selected + 'value="' + i + '">' + i + '</option>';
    }

    html += '</select>' +
        '   </div>' +
        '</div>';

    var node = Y.Node.create('<div>' + html + '</div>');

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

        // eslint-disable-next-line no-console
        console.log('M.availability_coursepayment.form.addedEvents');

        var root = Y.one('.availability-field');
        root.delegate('keyup', function() {
            // eslint-disable-next-line no-console
            console.log('Change detected');
            // The key point is this update call. This call will update
            // the JSON data in the hidden field in the form, so that it
            // includes the new value of the checkbox.
            M.core_availability.form.update();
        }, '.availability_coursepayment input');

        root.delegate('change', function() {
            // eslint-disable-next-line no-console
            console.log('Change detected');
            // The key point is this update call. This call will update
            // the JSON data in the hidden field in the form, so that it
            // includes the new value of the checkbox.
            M.core_availability.form.update();
        }, '.availability_coursepayment select');

    }

    return node;
};

M.availability_coursepayment.form.fillValue = function(value, node) {
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
M.availability_coursepayment.form.getValue = function(field, nodeoriginal) {
    var value;
    // Get field value.
    var node = nodeoriginal.one('[name=' + field + ']');
    if (node) {
        value = node.get('value');
        // Make , = .
        value = value.replace(/,/g, '.');
    }
    // eslint-disable-next-line no-console
    console.log('GetValue', field + ' = ' + value);

    // If it is not a valid positive number, return false.
    var reg = new RegExp('^\\d.+$');
    if (reg.test(value)) {
        if (field === 'vat') {
            // eslint-disable-next-line no-console
            console.log('vat:' + value);
            return parseInt(value);
        }

        return parseFloat(value).toFixed(2);
    }

    return value;
};

M.availability_coursepayment.form.fillErrors = function(errors, node) {
    var value = {};
    var reg = new RegExp('^[\\d.]+$');

    this.fillValue(value, node);

    // Check numeric values.
    if ((value.cost !== undefined)) {

        if (!reg.test(value.cost)) {
            // eslint-disable-next-line no-console
            console.log('cost - availability_coursepayment:error_invalidnumber');
            errors.push('availability_coursepayment:error_invalidnumber');
        }
    }

    // Check vat.
    if ((value.vat !== undefined)) {
        if (!reg.test(value.vat)) {
            // eslint-disable-next-line no-console
            console.log('vat - availability_coursepayment:error_invalidnumber ' + value.vat);
            errors.push('availability_coursepayment:error_invalidnumber');
        }
    }
};

}, '@VERSION@');
