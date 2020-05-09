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
 * Helper class
 *
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @package   availability_coursepayment
 * @copyright 2016 Mfreak.nl
 * @author    Luuk Verhoeven
 **/

namespace availability_coursepayment;

use stdClass;

defined('MOODLE_INTERNAL') || die();

/**
 * Class helper
 *
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @package   : availability_coursepayment
 * @copyright 2016 Mfreak.nl
 * @author    Luuk Verhoeven
 */
class helper {

    /**
     * get correct number format used for pricing
     *
     * @param float|int $number
     *
     * @return string
     */
    public static function price($number = 0.00) : string {
        return number_format(round($number, 2), 2, ',', ' ');
    }

    /**
     * pricing_from_cmid
     *
     * @param int $cmid
     *
     * @return stdClass
     * @throws \dml_exception
     */
    public static function pricing_from_cmid($cmid = 0) : stdClass {
        global $DB;

        $obj = new stdClass();
        $obj->price = 0;
        $obj->vat = 0;
        $obj->currency = 'EUR';

        $coursemodule = $DB->get_record('course_modules', ['id' => $cmid], '*', MUST_EXIST);
        $options = json_decode($coursemodule->availability);

        // Set availability.
        foreach ($options->c as $option) {
            if ($option->type == 'coursepayment') {
                $obj = $option;
                break;
            }
        }

        return $obj;
    }

    /**
     * get_cmid_info
     *
     * @param int $cmid
     * @param int $courseid
     *
     * @return bool|\cm_info
     * @throws \moodle_exception
     */
    public static function get_cmid_info($cmid = 0, $courseid = 0) {

        $modinfo = get_fast_modinfo($courseid);
        foreach ($modinfo->sections as $sectionnum => $section) {
            foreach ($section as $coursemoduleid) {
                if ($coursemoduleid == $cmid) {
                    return $modinfo->cms[$coursemoduleid];
                }
            }
        }

        return false;
    }

    /**
     * get_cmid_info
     *
     * @param int $sectionnumber
     * @param int $courseid
     *
     * @return stdClass
     * @throws \dml_exception
     */
    public static function get_section_info($sectionnumber = 0, $courseid = 0) : stdClass {
        global $DB;

        $section = $DB->get_record('course_sections', [
            'course' => $courseid,
            'section' => $sectionnumber,
        ], '*', MUST_EXIST);

        $courseformat = course_get_format($courseid);
        $defaultsectionname = $courseformat->get_default_section_name($section);

        $module = new stdClass();
        $module->name = $defaultsectionname;

        return $module;
    }

    /**
     * Check if a user can access a coursemodule
     *
     * @param int $cmid
     *
     * @return bool
     * @throws \dml_exception
     */
    public static function user_can_access_cmid($cmid = 0) : bool {
        global $USER, $DB;
        $row = $DB->get_record('enrol_coursepayment', [
            'userid' => $USER->id,
            'cmid' => $cmid,
            'status' => 1,
        ], 'id', IGNORE_MULTIPLE);

        return ($row) ? true : false;
    }

    /**
     * Get the price from the section availability_coursepayment
     *
     * @param int $sectionnumber
     * @param int $courseid
     *
     * @return stdClass
     * @throws \dml_exception
     */
    public static function pricing_from_section($sectionnumber = 0, $courseid = 0) : stdClass {

        global $DB;

        $obj = new stdClass();
        $obj->price = 0;
        $obj->vat = 0;
        $obj->currency = 'EUR';

        $coursemodule = $DB->get_record('course_sections', [
            'course' => $courseid,
            'section' => $sectionnumber,
        ], '*', MUST_EXIST);

        $options = json_decode($coursemodule->availability);

        // Set availability.
        foreach ($options->c as $option) {
            if ($option->type == 'coursepayment') {
                $obj = $option;
                break;
            }
        }

        return $obj;
    }

    /**
     * Check if the user can access this section
     *
     * @param     $sectionnumber
     * @param int $courseid
     *
     * @return bool
     * @throws \dml_exception
     */
    public static function user_can_access_section($sectionnumber, $courseid = 0) : bool {
        global $USER, $DB;
        $row = $DB->get_record('enrol_coursepayment', [
            'userid' => $USER->id,
            'section' => $sectionnumber,
            'courseid' => $courseid,
            'status' => 1,
        ], 'id', IGNORE_MULTIPLE);

        return ($row) ? true : false;
    }

}