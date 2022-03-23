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
 * Changes presets values via form
 *
 * @package    local_oc_course_creation
 * @auther     schindlerl
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @var $PAGE
 * @var $OUTPUT
 */

use local_oc_course_creation\form\string_form;
use local_oc_course_creation\manager;

require_once('../../config.php');
global $CFG;


$id = required_param('id', PARAM_INT);
$context = \context_system::instance();
//secure
redirect_if_major_upgrade_required();
require_login();
$hassiteconfig = has_capability('moodle/site:config', $context);
if ($hassiteconfig && moodle_needs_upgrading()) {
    redirect(new moodle_url('/admin/index.php'));
}
require_capability('local/oc_course_creation:handle_presets', $context);

$PAGE->set_url(new moodle_url('/local/oc_course_creation/edit_preset_value.php'));
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('edit_preset_value_title', 'local_oc_course_creation'));
$PAGE->set_heading(get_site()->fullname);

$mform = new string_form();
$manager = new manager();

if ($mform->is_cancelled()) {
    //go back to manage page
    redirect($CFG->wwwroot . '/local/oc_course_creation/list_preset_values.php');
} else if ($fromform = $mform->get_data()) {
    //insert the data in the db

    $manager->update_value($fromform->id, $fromform->string, $fromform->type_id);
    redirect($CFG->wwwroot . '/local/oc_course_creation/list_preset_values.php');

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