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
 * @package    local_coursetemplatewizard
 * @copyright   2025 Oncampus GmbH
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
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

$templatetargetcourseexceptionsconfig = get_config('local_coursetemplatewizard', 'templatetargetcourseexceptions');
$templatetargetcourseexceptions = explode(',', $templatetargetcourseexceptionsconfig);
if (in_array($targetcourseid, $templatetargetcourseexceptions, true)) {
    return;
}

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

// Checking, if the Course template comes from the configured template course category.
$templatecoursecategoryid = get_config('local_coursetemplatewizard', 'templatecoursecategoryid');
$allowedcat = null;
if (!empty($templatecoursecategoryid) && ctype_digit((string)$templatecoursecategoryid)) {
    try {
        $allowedcat = \core_course_category::get((int)$templatecoursecategoryid, IGNORE_MISSING, true);
    } catch (\Throwable $e) {
        $allowedcat = null;
    }
}
if (!$allowedcat) {
    throw new \moodle_exception(
        'error',
        'local_coursetemplatewizard',
        '',
        null,
        'Template category missing or misconfigured.'
    );
}

$urlparams = [
    'templateid' => $templateid,
    'targetcourseid' => $targetcourseid,
];
$url = new moodle_url('/local/coursetemplatewizard/handle_copy_form.php', $urlparams);

// Seite.
$PAGE->set_url($url);
$PAGE->set_pagelayout('standard');
$PAGE->set_context($systemcontext);
$PAGE->set_title(get_string('creation_page_title', 'local_coursetemplatewizard'));

$templatecourse = get_course($templateid);
// Casting $allowedcat->id to int has to be executed here, because it is a string, despite the type declaration of the property!
if ((int)$templatecourse->category !== (int)$allowedcat->id) {
    throw new moodle_exception(
        'error',
        'local_coursetemplatewizard',
        'nopermissions',
        null,
        'Template not in allowed category'
    );
}
$targetcourse = get_course($targetcourseid);
$mform = new template_utilization_form($url->out(false), [
        'templatecourse' => $templatecourse,
        'targetcourse' => $targetcourse,
]);

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/local/coursetemplatewizard/list_courses_to_copy.php', [
        'targetcourseid' => $targetcourseid,
    ]));
}

if ($mdata = $mform->get_data()) {
    $mdata->templateid     = $templateid;
    $mdata->targetcourseid = (int)$mdata->targetcourseid;

    if (empty($mdata->confirmoverwrite)) {
        throw new moodle_exception('confirm_overwrite_required', 'local_coursetemplatewizard');
    }

    $targetctx = context_course::instance($mdata->targetcourseid);
    require_capability('local/coursetemplatewizard:use', $targetctx);

    // Optional: Handling course image usage.
    $mdata->hasoverview    = false;
    $mdata->overviewdraftid = 0;
    if (!empty($mdata->overviewfiles_filemanager)) {
        $mdata->overviewdraftid = (int)$mdata->overviewfiles_filemanager;
        $info = file_get_draft_area_info($mdata->overviewdraftid, true);
        if (!empty($info['filecount'])) {
            $mdata->hasoverview = true;
        }
    }

    $SESSION->coursetemplatewizardformdata = $mdata;

    redirect(
        new moodle_url(
            '/local/coursetemplatewizard/show_template_restore_process.php',
            [
                    'templateid' => $templateid,
                    'targetcourseid' => $targetcourseid,
            ]
        )
    );
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('creation_page_title', 'local_coursetemplatewizard'));

if ($mform->is_submitted() && $targetcourseid) {
    $PAGE->set_url(new moodle_url(
        '/local/coursetemplatewizard/handle_copy_form.php',
        ['templateid' => $templateid, 'targetcourseid' => $targetcourseid]
    ));
}
$mform->display();

echo $OUTPUT->footer();
