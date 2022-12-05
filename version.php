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
 * Version info.
 *
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @package   : availability_coursepayment
 * @copyright 2016 Mfreak.nl
 * @author    Luuk Verhoeven
 **/
defined('MOODLE_INTERNAL') || die();

$plugin->component = 'availability_coursepayment';
$plugin->version = 2022120200;
$plugin->requires = 2014050800;
$plugin->maturity = MATURITY_STABLE;
$plugin->release = '4.0.5';
$plugin->dependencies = [
    'enrol_coursepayment' => 2022120200,
];
