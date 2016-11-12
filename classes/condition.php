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
 * Activity completion condition.
 *
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @package   : availability_coursepayment
 * @copyright 2016 MoodleFreak.com
 * @author    Luuk Verhoeven
 **/

namespace availability_coursepayment;

defined('MOODLE_INTERNAL') || die();

class condition extends \core_availability\condition {

    /**
     * How many seconds between log counts in the calculation
     *
     * @const int SECONDS_BETWEEN
     */
    const SECONDS_BETWEEN = 1800;

    /**
     * Minimal minutes needed to have this condition
     *
     * @var int $minimal_minutes
     */
    protected $minimal_minutes = 0;

    public function __construct($structure) {

        if (isset($structure->minimal_minutes) && is_int($structure->minimal_minutes)) {
            // set a number
            $this->minimal_minutes = abs($structure->minimal_minutes);
        }else {
            throw new \coding_exception('Missing or invalid ->minimal_minutes for learningtime condition');
        }
    }

    /**
     * Saves tree data back to a structure object.
     *
     * @return \stdClass Structure object (ready to be made into JSON format)
     */
    public function save() {
        // Save back the data into a plain array similar to $structure above.
        return (object)array('type' => 'learningtime', 'minimal_minutes' => $this->minimal_minutes);
    }

    /**
     * Returns a JSON object which corresponds to a condition of this type.
     *
     * Intended for unit testing, as normally the JSON values are constructed
     * by JavaScript code.
     *
     * @param string $minimal_minutes
     * @return stdClass Object representing condition
     */
    public static function get_json($minimal_minutes) {
        return (object)array('type' => 'learningtime', 'minimal_minutes' => (int)$minimal_minutes);
    }


    /**
     * Determines whether a particular item is currently available
     * according to this availability condition.
     *
     * If implementations require a course or modinfo, they should use
     * the get methods in $info.
     *
     * The $not option is potentially confusing. This option always indicates
     * the 'real' value of NOT. For example, a condition inside a 'NOT AND'
     * group will get this called with $not = true, but if you put another
     * 'NOT OR' group inside the first group, then a condition inside that will
     * be called with $not = false. We need to use the real values, rather than
     * the more natural use of the current value at this point inside the tree,
     * so that the information displayed to users makes sense.
     *
     * @param bool $not                     Set true if we are inverting the condition
     * @param \core_availability\info $info Item we're checking
     * @param bool $grabthelot              Performance hint: if true, caches information
     *                                      required for all course-modules, to make the front page and similar
     *                                      pages work more quickly (works only for current user)
     * @param int $userid                   User ID to check availability for
     *
     * @return bool True if available
     */
    public function is_available($not, \core_availability\info $info, $grabthelot, $userid) {

        $course = $info->get_course();
        if ($this->minimal_minutes > 0) {
            $spentseconds = $this->get_user_time($userid, $course->id);
            if ($spentseconds < ($this->minimal_minutes * 60)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Obtains a string describing this restriction (whether or not
     * it actually applies). Used to obtain information that is displayed to
     * students if the activity is not available to them, and for staff to see
     * what conditions are.
     *
     * The $full parameter can be used to distinguish between 'staff' cases
     * (when displaying all information about the activity) and 'student' cases
     * (when displaying only conditions they don't meet).
     *
     * If implementations require a course or modinfo, they should use
     * the get methods in $info.
     *
     * The special string <AVAILABILITY_CMNAME_123/> can be returned, where
     * 123 is any number. It will be replaced with the correctly-formatted
     * name for that activity.
     *
     * @param bool $full Set true if this is the 'full information' view
     * @param bool $not  Set true if we are inverting the condition
     * @param info $info Item we're checking
     *
     * @return string Information string (for admin) about all restrictions on
     *   this item
     */
    public function get_description($full, $not, \core_availability\info $info) {

        // This function just returns the information that shows about
        // the condition on editing screens. Usually it is similar to
        // the information shown if the user doesn't meet the
        // condition (it does not depend on the current user).
        // global $USER;
        // $course = $info->get_course();
        $obj = new \stdClass();
        $obj->minimal_minutes = $this->time_format($this->minimal_minutes);

        if ($this->minimal_minutes > 0) {
            // we need to check if the user has this condition
            // $obj->minutes = $this->get_user_time($USER->id , $course->id);
        }

        return get_string('require_condition', 'availability_coursepayment', $obj);
    }

    /**
     * get_user_time
     *
     * @param int $userid
     * @param int $courseid
     *
     * @return int return the minutes a user spent on this
     */
    protected function get_user_time($userid = 0, $courseid = 0) {
        global $DB;
        //@todo maybe some sort of caching or writing stats to DB
        static $userHolder = array();

        if(isset($userHolder[$userid][$courseid])){
            return $userHolder[$userid][$courseid];
        }

        $set = array();
        $sql = 'SELECT id, timecreated as time FROM {logstore_standard_log} WHERE userid = ? AND courseid = ? ORDER BY id DESC';

        $results = $DB->get_records_sql($sql, array($userid, $courseid));
        foreach ($results as $result) {
            $set[$result->time] = $result;
        }

        $sql = 'SELECT t.id, t.timemodified as time FROM {scorm_scoes_track} t
            JOIN {scorm} s ON s.id = t.scormid
            WHERE t.userid = ?
            AND  s.course = ?
            ORDER BY t.id DESC';

        $results = $DB->get_records_sql($sql, array($userid, $courseid));
        foreach ($results as $result) {
            $set[$result->time] = $result;
        }
        krsort($set, SORT_NUMERIC);

        // calculate time
        $last = false;
        $totalTime = 0;

        foreach ($set as $log) {
            if (!empty($last)) {
                $sum = ($last->time - $log->time);
                if ($sum <= self::SECONDS_BETWEEN) {
                    $totalTime += $sum;
                }
                // else there is to much between a previous item
            }

            $last = $log;
        }
        unset($set , $results);

        $userHolder[$userid][$courseid] = $totalTime;
        return $totalTime;
    }

    /**
     * convert minutes to better readable format
     *
     * @param int $minutes
     *
     * @return string
     */
    protected function time_format($minutes = 0) {
        return $this->time_elapsed_string($minutes * 60);
    }


    /**
     * time_elapsed_string
     *
     * @param int $seconds
     *
     * @return string
     */
    protected function time_elapsed_string($seconds) {
        $etime = $seconds;
        if ($etime < 1) {
            return '0 seconds';
        }

        $a = array(
            12 * 30 * 24 * 60 * 60 => 'year',
            30 * 24 * 60 * 60 => 'month',
            24 * 60 * 60 => 'day',
            60 * 60 => 'hour',
            60 => 'minute',
            1 => 'second'
        );

        if (!empty($mode)) {
            foreach ($a as $k => $v) {
                if ($v == $mode) {
                    $a = array($k => $v);
                    break;
                }
            }
        }

        foreach ($a as $secs => $str) {
            $d = $etime / $secs;
            if ($d >= 1) {
                $r = round($d);

                return $r . ' ' . get_string($str . ($r > 1 ? 's' : ''));
            }
        }
    }

    /**
     * Obtains a representation of the options of this condition as a string,
     * for debugging.
     *
     * @return string Text representation of parameters
     */
    protected function get_debug_string() {
        // This function is only normally used for unit testing and
        // stuff like that. Just make a short string representation
        // of the values of the condition, suitable for developers.
        return ($this->minimal_minutes > 0) ? 'Minimal_minutes ON' : 'Minimal_minutes OFF';
    }
}