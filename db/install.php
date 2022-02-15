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

use local_oc_course_creation\manager;
function xmldb_local_oc_course_creation_install() {
    global $CFG, $DB;
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

    $record_type1 = new stdClass();
    $record_type2 = new stdClass();


    $record_type1->type = "Semester";
    $record_type1->rank = 1;
    $record_type1->id = 1;

    $record_type2->type = "Year";
    $record_type2->rank = 2;
    $record_type2->id = 2;

    $DB->insert_records('oc_course_creation_type', [$record_type1,$record_type2]);

    $record_type1_value1 = new stdClass();
    $record_type1_value2 = new stdClass();

    $record_type2_value1 = new stdClass();
    $record_type2_value2 = new stdClass();

    $record_type1_value1->string = get_string('record_type1_value1', 'local_oc_course_creation');
    $record_type1_value1->type_id = $record_type1->id;

    $record_type1_value2->string = get_string('record_type1_value2', 'local_oc_course_creation');
    $record_type1_value2->type_id = $record_type1->id;

    $record_type2_value1->string =date("y");
    $record_type2_value1->type_id = $record_type2->id;

    $record_type2_value2->string =date("y") +1;
    $record_type2_value2->type_id = $record_type2->id;

    $DB->insert_records('oc_course_creation_value', [$record_type1_value1,$record_type1_value2,$record_type2_value1,$record_type2_value2]);
}

