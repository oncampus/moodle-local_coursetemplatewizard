<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * @package     local_oc_course_creation
 * @category    admin
 * @copyright   2021 Laurenz Schindler <Laurenz.Schindler@oncampus.de>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG, $DB;
require_once($CFG->dirroot . "/config.php");
require_once($CFG->dirroot . '/course/lib.php');

/*
 * Creates a default course category
 */

$data = new stdClass();
$data->name = get_string('plugin_categoryname', 'local_oc_course_creation');
$data->description = 'This is the default course category for course templates oc course creation will use.';
$data->idnumber = '';
$data->visible = '0';
core_course_category::create($data);

use local_oc_course_creation\manager;
$manager= new manager();

$record_course_name = new stdClass();
$record_year = new stdClass();
$record_semester_So = new stdClass();
$record_semester_Wi = new stdClass();

$record_course_name->type = "Kursbezeichnung";
$record_course_name->string = "Informatik";

$record_year->type = "Jahr";
$record_year->string = date("y");

$record_semester_So->type = 'Semester';
$record_semester_So->string = 'SoSe';

$record_semester_Wi->type = "Semester";
$record_semester_Wi->string = 'WiSe';

$DB->insert_records('oc_course_creation',[$record_course_name,$record_year,$record_semester_So,$record_semester_Wi]);