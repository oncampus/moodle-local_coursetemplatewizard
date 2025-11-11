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
 * @package    local_ocbsbcoursecreation
 * @copyright   2025 Oncampus GmbH
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\output\notification;
use local_ocbsbcoursecreation\form\modified_copy_form;
use local_ocbsbcoursecreation\manager;

require('../../config.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/local/ocbsbcoursecreation/lib.php');

$templateid = required_param('templateid', PARAM_INT);

// Nur Navigation-Lock unterscheiden (optional).
$fixedtargetidfromget = isset($_GET['targetcourseid']) ? (int)$_GET['targetcourseid'] : 0;
$currenttargetid      = optional_param('targetcourseid', 0, PARAM_INT);

require_login();

// Check für die targetcourseid, damit Bildungspläne, Austauschforum etc. nicht zufällig benutzt werden!
$redirecturl = new moodle_url('/course/view.php', ['id' => $fixedtargetidfromget]);
if ($fixedtargetidfromget && !local_ocbsbcoursecreation_is_course_in_school($fixedtargetidfromget)) {
    redirect($redirecturl, get_string('errorcoursenotinschool', 'local_ocbsbcoursecreation'), null, notification::NOTIFY_ERROR);
}

$systemcontext = context_system::instance();

// Whitelist: Vorlage MUSS aus der konfigurierten Kategorie stammen.
$setcoursecategory = get_config('local_ocbsbcoursecreation', 'categoryid');
$allowedcat = null;

if (!empty($setcoursecategory) && ctype_digit((string)$setcoursecategory)) {
    try {
        $allowedcat = \core_course_category::get((int)$setcoursecategory, IGNORE_MISSING, true);
    } catch (\Throwable $e) {
        $allowedcat = null;
    }
}

if (!$allowedcat) {
    throw new \moodle_exception(
        'error',
        'local_ocbsbcoursecreation',
        '',
        null,
        'Configured template category not found.'
    );
}

// Gehört die Vorlage wirklich zu dieser Kategorie?
$templatecourse = get_course($templateid);
if ((int)$templatecourse->category !== (int)$allowedcat->id) {
    throw new required_capability_exception(
        context_course::instance($templateid),
        'local/ocbsbcoursecreation:use', // Virtuelle Capability fürs Log – nicht wirklich nötig.
        'nopermissions',
        'Template not in allowed category'
    );
}

// Kursliste einschränken auf Kurse, in denen der Nutzer überhaupt eingeschrieben ist (Trainer/Editingteacher o.ä.).
$usercourses = enrol_get_users_courses($USER->id, true, 'id, fullname');
$courselist  = [];

if ($fixedtargetidfromget && isset($usercourses[$fixedtargetidfromget])) {
    $courselist[$fixedtargetidfromget] = s($usercourses[$fixedtargetidfromget]->fullname);
} else {
    if (has_capability('local/ocbsbcoursecreation:handle_presets', $systemcontext)) {
        foreach ($usercourses as $uc) {
            $courselist[$uc->id] = s($uc->fullname);
        }
    } else {
        redirect(
            $redirecturl,
            get_string('errornotteacherincourse', 'local_ocbsbcoursecreation'),
            null,
            notification::NOTIFY_ERROR
        );
    }
}

$urlparams = ['templateid' => $templateid];
if ($fixedtargetidfromget) {
    $urlparams['targetcourseid'] = $fixedtargetidfromget;
}
$url = new moodle_url('/local/ocbsbcoursecreation/handle_copy_form.php', $urlparams);

// Seite.
$PAGE->set_url($url);
$PAGE->set_pagelayout('standard');
$PAGE->set_context($systemcontext);
$PAGE->set_title(get_string('creation_page_title', 'local_ocbsbcoursecreation'));

// Formular.
$mform = new modified_copy_form($url->out(false), [
    'courses'            => $courselist,
    'course'             => $templatecourse,
    'fixedtargetid'      => $fixedtargetidfromget,
    'locktarget_on_load' => (bool)$fixedtargetidfromget,
]);

if ($currenttargetid && isset($courselist[$currenttargetid])) {
    $mform->set_data((object)['targetcourseid' => $currenttargetid]);
};

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('creation_page_title', 'local_ocbsbcoursecreation'));

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/local/ocbsbcoursecreation/list_courses_to_copy.php'));
} else if ($mdata = $mform->get_data()) {
    $mdata->templateid     = $templateid;
    $mdata->targetcourseid = (int)$mdata->targetcourseid;

    if (empty($mdata->confirmoverwrite)) {
        throw new moodle_exception('confirm_overwrite_required', 'local_ocbsbcoursecreation');
    }

    // Harte Ziel-Rechte: Nutzer muss seinen Zielkurs bearbeiten dürfen.
    $targetctx = context_course::instance($mdata->targetcourseid);
    require_capability('moodle/course:update', $targetctx);
    // Für Restore ins bestehende Kurskontext ist formal auch restore-Recht sinnvoll:
    // Wenn ihr den Restore als Admin fahrt (siehe Manager unten), könnt ihr hier
    // Code: moodle/restore:restorecourse` weglassen. Sicherheitshalber:
    // Code: require_capability('moodle/restore:restorecourse', $targetctx);.

    // Optional: Kursbild-Handling (wie gehabt).
    $mdata->hasoverview    = false;
    $mdata->overviewdraftid = 0;
    if (!empty($mdata->overviewfiles_filemanager)) {
        $mdata->overviewdraftid = (int)$mdata->overviewfiles_filemanager;
        $info = file_get_draft_area_info($mdata->overviewdraftid, true);
        if (!empty($info['filecount'])) {
            $mdata->hasoverview = true;
        }
    }

    // Kopiervorgang ausführen (als Admin, aber QUELLE strikt auf erlaubte Kategorie begrenzt).
    $manager = new manager();
    $newcourseid = $manager->replace_course_with_template($mdata);
    // Update courseid if Schuldock/IAM Kurs.
    local_ocbsbcoursecreation_update_iamschool($mdata->targetcourseid, $newcourseid);
    redirect(new moodle_url('/course/view.php', ['id' => $newcourseid]));
} else {
    if ($mform->is_submitted() && $currenttargetid) {
        $PAGE->set_url(new moodle_url(
            '/local/ocbsbcoursecreation/handle_copy_form.php',
            ['templateid' => $templateid, 'targetcourseid' => $currenttargetid]
        ));
    }
    $mform->display();
}

echo $OUTPUT->footer();
