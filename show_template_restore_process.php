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
 * Handle copy form: pick target course and apply template.
 *
 * @package     local_coursetemplatewizard
 * @copyright   2025 oncampus GmbH <support@oncampus.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\output\notification;
use local_coursetemplatewizard\form\template_utilization_form;
use local_coursetemplatewizard\template_utilization_manager;

require('../../config.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/local/coursetemplatewizard/lib.php');

require_login();
$templateid = required_param('templateid', PARAM_INT);
$targetcourseid = required_param('targetcourseid', PARAM_INT);

$systemcontext = context_system::instance();
$targetcoursecontext = context_course::instance($targetcourseid);
if (!has_capability('local/coursetemplatewizard:use', $targetcoursecontext)) {
    throw new required_capability_exception(
        $targetcoursecontext,
        'local/coursetemplatewizard:use',
        'nopermissions',
        ''
    );
}

// Redirecting to originating form page, if no form data has been stored.
$cache = cache::make('local_coursetemplatewizard', 'formdata');
$mdata = $cache->get('formdata');
if (empty($mdata)) {
    redirect(
        new moodle_url('/local/coursetemplatewizard/handle_copy_form.php', [
            'templateid' => $templateid,
            'targetcourseid' => $targetcourseid,
        ]),
        get_string('template_restore_page_no_data', 'local_coursetemplatewizard')
    );
}
$cache->delete('formdata');

$urlparams = [
        'templateid' => $templateid,
        'targetcourseid' => $targetcourseid,
];
$url = new moodle_url(
    '/local/coursetemplatewizard/show_template_restore_process.php',
    $urlparams
);

// Seite.
$PAGE->set_url($url);
$PAGE->set_pagelayout('standard');
$PAGE->set_context($systemcontext);
$PAGE->set_title(get_string('template_restore_page_title', 'local_coursetemplatewizard'));

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('template_restore_page_title', 'local_coursetemplatewizard'));

// Perform the copy operation (as an administrator, but strictly limit the SOURCE to the permitted category).
$templateutilizationmanager = new template_utilization_manager();
$templateutilizationmanager->replace_course_with_template_and_render_progress($targetcoursecontext, $targetcourseid, $templateid);
$templateutilizationmanager->transfer_picture_from_form_to_course($mdata, $targetcoursecontext);
$templateutilizationmanager->transfer_summary_from_form_to_course($mdata, $targetcourseid, $targetcoursecontext);

echo $OUTPUT->footer();
