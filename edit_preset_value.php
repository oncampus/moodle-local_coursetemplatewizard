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
 * message file description here.
 *
 * @package    local_message
 * @copyright  2021 SysBind Ltd. <service@sysbind.co.il>
 * @auther     schindlerl
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_oc_course_creation\form\string_form;
use local_oc_course_creation\form;
use local_oc_course_creation\manager;

require('../../config.php');

global $CFG;

$id = required_param('id', PARAM_INT);
$PAGE->set_url(new moodle_url('/local/oc_course_creation/edit_preset_value.php'));
$PAGE->set_context(\context_system::instance());
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('edit_preset_value_title', 'local_oc_course_creation'));
$mform = new string_form();
$manager = new manager();

if ($mform->is_cancelled()) {
    //go back to manage page
    redirect($CFG->wwwroot . '/local/oc_course_creation/list_preset_values.php');
} else if ($fromform = $mform->get_data()) {
    //insert the data in the db

    if ($fromform->id) {
        $manager->update_value($fromform->id, $fromform->string, $fromform->type_id);
        redirect($CFG->wwwroot . '/local/oc_course_creation/list_preset_values.php');
    }
}

if ($id) {
    $preset = $manager->get_value_by_id($id);
    if (!$preset) {
        throw new invalid_parameter_exception('Preset not found.');
    }
    $mform->set_data($preset);
}

echo $OUTPUT->header();

$mform->display();

echo $OUTPUT->footer();