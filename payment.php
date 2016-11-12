<?php
/**
 * File: payment.php
 * Encoding: UTF8
 *
 * @package: availability_coursepayment
 *
 * @Version: 1.0.0
 * @Since  12-11-2016
 * @Author : MoodleFreak.com | Ldesign.nl - Luuk Verhoeven
 **/
require('../../../config.php');
$cmid = required_param('cmid', PARAM_INT);
$courseid = required_param('courseid', PARAM_INT);
$gatewaymethod = optional_param('gateway', false, PARAM_ALPHA);

$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

require_login($course);
$PAGE->set_url('/availability/condition/coursepayment/payment.php', array(
    'courseid' => $course->id,
    'id' => $cmid
));

// Set main gateway javascript
$jsmodule = array(
    'name' => 'enrol_coursepayment_gateway',
    'fullpath' => '/enrol/coursepayment/js/gateway.js',
    'requires' => array('node', 'io')
);

$PAGE->requires->js_init_call('M.enrol_coursepayment_gateway.init', array(
    $CFG->wwwroot . '/enrol/coursepayment/ajax.php',
    sesskey(),
    $course->id
), false, $jsmodule);


echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'enrol_coursepayment'));

// @TODO get pricing from the condition.
echo '<div align="center">
                            <h3 class="coursepayment_instancename">' . $name . '</h3>
                            <p><b>' . get_string("cost") . ': 
                            <span id="coursepayment_cost">' . $config->localisedcost . '</span> ' .
    $instance->currency . ' </b></p>
                          </div>';

$gateway = 'enrol_coursepayment_mollie';
if (!class_exists($gateway)) {
    throw Exception('Error');
}

/* @var enrol_coursepayment_gateway $gateway */
$gateway = new $gateway();
$gateway->set_instanceconfig($config);

// @TODO add new path to javascript.
echo $gateway->order_form();

echo $OUTPUT->footer();