<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Payment page for a activity
 *
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @package   availability_coursepayment
 * @copyright 2016 MoodleFreak.com
 * @author    Luuk Verhoeven
 **/
require('../../../config.php');
$cmid = optional_param('cmid', false, PARAM_INT);
$section = optional_param('section', false, PARAM_INT);
$contextlevel = required_param('contextlevel', PARAM_INT);
$courseid = required_param('courseid', PARAM_INT);
$method = optional_param('method', false, PARAM_ALPHA);

$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

require_login($course);
$PAGE->set_url('/availability/condition/coursepayment/payment.php', array(
    'courseid' => $course->id,
    'cmid' => $cmid,
    'method' => $method,
    'section' => $section,
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

switch ($contextlevel) {

    case CONTEXT_MODULE:

        // Check if user already can access the content.
        if (\availability_coursepayment\helper::user_can_access_cmid($cmid)) {
            redirect(new moodle_url('/course/view.php', ['id' => $course->id]));
        }

        // Get more info.
        $module = \availability_coursepayment\helper::get_cmid_info($cmid, $course->id);
        $pricing = \availability_coursepayment\helper::pricing_from_cmid($cmid);

        // Check if we are redirecting.
        if (!$method) {
            echo $OUTPUT->header();
            echo $OUTPUT->heading(get_string('pluginname', 'enrol_coursepayment'));
            echo '<div align="center">
                    <h3 class="coursepayment_instancename">' . $module->name . '</h3>
                    <p><b>' . get_string("cost") . ': 
                    <span id="coursepayment_cost">' . \availability_coursepayment\helper::price($pricing->cost) . '</span> ' .
                            get_string('currency:' . strtolower($pricing->currency), 'availability_coursepayment') . ' </b></p>
                  </div>';
        }

        break;

    case CONTEXT_COURSE:
        // Check if user already can access the content.
        if (\availability_coursepayment\helper::user_can_access_section($section , $course->id)) {
            redirect(new moodle_url('/course/view.php', ['id' => $course->id]));
        }

        // Get more info.
        $module = \availability_coursepayment\helper::get_section_info($section, $course->id);
        $pricing = \availability_coursepayment\helper::pricing_from_section($section, $course->id);


        // Check if we are redirecting.
        if (!$method) {
            echo $OUTPUT->header();
            echo $OUTPUT->heading(get_string('pluginname', 'enrol_coursepayment'));
            echo '<div align="center">
                    <h3 class="coursepayment_instancename">' . $module->name . '</h3>
                    <p><b>' . get_string("cost") . ': 
                    <span id="coursepayment_cost">' . \availability_coursepayment\helper::price($pricing->cost) . '</span> ' .
                get_string('currency:' . strtolower($pricing->currency), 'availability_coursepayment') . ' </b></p>
                  </div>';
        }
        break;
}

/* @var enrol_coursepayment_gateway $gateway */
$gateway = new enrol_coursepayment_mollie();
$gateway->set_instanceconfig([
    'is_activity' => true,
    'userid' => $USER->id,
    'userfullname' => fullname($USER),
    'coursename' => $module->name, // $Module can also be a section
    'locale' => $USER->lang,
    'currency' => $pricing->currency,
    'cost' => $pricing->cost,
    'cost' => $pricing->cost,
    'courseid' => $course->id,
    'vatpercentage' => $pricing->vat,
    'customint1' => $pricing->vat,
    'instanceid' => 0,
    'cmid' => $cmid,
    'section' => $section,
    'contextlevel' => $contextlevel,
]);

// Payment form.
echo $gateway->order_form();

// Moodle Footer.
echo $OUTPUT->footer();