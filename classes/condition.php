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
     * @var int $cost
     */
    protected $cost = 0;

    /**
     * @var string
     */
    protected $currency = 'EUR';

    /**
     * @var int
     */
    protected $vat = 21;

    public function __construct($structure) {

        if (property_exists($structure, 'cost')) {
            $this->cost = abs($structure->cost);
        } else {
            $this->cost = 0;
        }

        if (property_exists($structure, 'currency')) {
            $this->currency = $structure->currency;
        } else {
            $this->currency = 'EUR';
        }

        if (property_exists($structure, 'vat')) {
            $this->vat = $structure->vat;
        } else {
            $this->vat = 21;
        }

        // Throw errors??
    }

    /**
     * Saves tree data back to a structure object.
     *
     * @return \stdClass Structure object (ready to be made into JSON format)
     */
    public function save() {
        // Save back the data into a plain array similar to $structure above.
        return (object)array(
            'type' => 'coursepayment',
            'cost' => $this->cost,
            'currency' => $this->currency,
            'vat' => $this->vat,
        );
    }

    /**
     * Returns a JSON object which corresponds to a condition of this type.
     *
     * Intended for unit testing, as normally the JSON values are constructed
     * by JavaScript code.
     *
     * @param int|string $cost
     * @param int $vat
     * @param string $currency
     *
     * @return stdClass Object representing condition
     */
    public static function get_json($cost = 0, $vat = 21, $currency = 'EUR') {
        return (object)array(
            'type' => 'coursepayment',
            'cost' => (int)$cost,
            'vat' => (int)$vat,
            'currency' => $currency,
        );
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

        // @TODO validate that a user has paid.
        $ispaid = false; // @TODO a real check on the db
        if(!$ispaid){
            return false;
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
        // $course = $info->get_course();
        $obj = new \stdClass();
        $obj->cost = $this->price($this->cost);
        $obj->currency = get_string('currency:' . strtolower($this->currency), 'availability_coursepayment');
        $obj->vat = $this->vat;
        $obj->btn = \html_writer::link(new \moodle_url('/availability/condition/coursepayment/payment.php' , [
            'cmid' => $info->get_context()->instanceid,
            'courseid' => $info->get_course()->id,
                ]) , get_string('btn:purchase' , 'availability_coursepayment') , [
                    'class' => 'btn btn-primary'
        ]);
        
        return get_string('require_condition', 'availability_coursepayment', $obj);
    }

    /**
     * get correct number format used for pricing
     *
     * @param float|int $number
     *
     * @return string
     */
    public function price($number = 0.00) {
        return number_format(round($number, 2), 2, ',', ' ');
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
        return ($this->cost > 0) ? 'cost ON' : 'cost OFF';
    }
}