/**
 * JavaScript for form editing grade conditions.
 *
 * @module moodle-availability_learningtime-form
 */
M.availability_learningtime = M.availability_learningtime || {};
/**
 * @class M.availability_learningtime.form
 * @extends M.core_availability.plugin
 */
M.availability_learningtime.form = Y.Object(M.core_availability.plugin);
/**
 * Initialises this plugin.
 *
 * @method initInner
 * @param {Array} param Array of objects
 */
M.availability_learningtime.form.initInner = function(params) {
    console.log('M.availability_learningtime');
};

M.availability_learningtime.form.getNode = function(json) {
    // This function does the main work. It gets called after the user
    // chooses to add an availability restriction of this type. You have
    // to return a YUI node representing the HTML for the plugin controls.

    console.log(json);

    var strings = M.str.availability_learningtime;
    var html = '<label>' + strings.title + '</label> <input type="text" name="minimal_minutes" title="' +
        strings.label_minimal_minutes + '"/></label> '+ strings.minutes +' ('  + strings.label_minimal_minutes + ')</span>';
    var node = Y.Node.create('<span>' + html + '</span>');

    // Set initial values based on the value from the JSON data in Moodle
    // database. This will have values undefined if creating a new one.

    if (json.minimal_minutes !== undefined){
        node.one('input[name=minimal_minutes]').set('value', json.minimal_minutes);
    }


    // Add event handlers (first time only). You can do this any way you
    // like, but this pattern is used by the existing code.
    if (!M.availability_learningtime.form.addedEvents) {
        M.availability_learningtime.form.addedEvents = true;
        var root = Y.one('#fitem_id_availabilityconditionsjson');
        root.delegate('change', function() {
            // The key point is this update call. This call will update
            // the JSON data in the hidden field in the form, so that it
            // includes the new value of the checkbox.
            M.core_availability.form.update();
        }, '.availability_learningtime input');
    }

    return node;
};

M.availability_learningtime.form.fillValue = function(value, node) {
    // This function gets passed the node (from above) and a value
    // object. Within that object, it must set up the correct values
    // to use within the JSON data in the form. Should be compatible
    // with the structure used in the __construct and save functions
    // within condition.php.

    value.minimal_minutes = this.getValue('minimal_minutes', node);
};


/**
 * Gets the numeric value of an input field. Supports decimal points (using
 * dot or comma).
 *
 * @method getValue
 * @return {Number|String} Value of field as number or string if not valid
 */
M.availability_learningtime.form.getValue = function(field, node) {
    // Get field value.
    var value = node.one('input[name=' + field + ']').get('value');

    // If it is not a valid positive number, return false.
    var reg = new RegExp('^\\d+$');
    if (reg.test(value)) {
        console.log('Return:' + parseInt(value));
        return parseInt(value);
    }

    console.log('Failed:' + value);
    return 0;
};


M.availability_learningtime.form.fillErrors = function(errors, node) {
    var value = {};
    this.fillValue(value, node);
    console.log(value)
    // Check numeric values.
    if ((value.minimal_minutes !== undefined && typeof(value.minimal_minutes) === 'string')) {
        errors.push('availability_learningtime:error_invalidnumber');
    }
};