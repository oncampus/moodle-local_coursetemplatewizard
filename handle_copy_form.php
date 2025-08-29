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

use local_ocbsbcoursecreation\form\modified_copy_form;
use local_ocbsbcoursecreation\manager;

require('../../config.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->libdir . '/formslib.php');

$templateid = required_param('templateid', PARAM_INT);

// GET-Param getrennt ermitteln, um echte „Lock aus Navigation“-Fälle zu unterscheiden.
$fixedtargetidfromget = isset($_GET['targetcourseid']) ? (int)$_GET['targetcourseid'] : 0;

// Aktuelle Auswahl aus Request (POST oder GET) – für Defaults & URL-Persistenz.
$currenttargetid = optional_param('targetcourseid', 0, PARAM_INT);

require_login();

$systemcontext = context_system::instance();

// Kursliste der Kurse, in denen der/die Nutzer:in Trainer ist.
$usercourses = enrol_get_users_courses($USER->id, true, 'id, fullname');
$courselist  = [];

// Falls Zielkurs aus Navigation (GET) kam und der/die Nutzer:in dort Trainer ist -> Liste einschränken.
if ($fixedtargetidfromget && isset($usercourses[$fixedtargetidfromget])) {
    $courselist[$fixedtargetidfromget] = format_string($usercourses[$fixedtargetidfromget]->fullname);
} else {
    foreach ($usercourses as $uc) {
        $courselist[$uc->id] = format_string($uc->fullname);
    }
}

// URL nur mit GET-Preselection bauen (Navigation). Wichtig: out(false) geben wir beim Formular weiter.
$urlparams = ['templateid' => $templateid];
if ($fixedtargetidfromget) {
    $urlparams['targetcourseid'] = $fixedtargetidfromget;
}
$url = new moodle_url('/local/ocbsbcoursecreation/handle_copy_form.php', $urlparams);

// Formular erstellen. Lock nur, wenn GET-Preselection existiert.
$mform = new modified_copy_form($url->out(false), [
    'courses'            => $courselist,
    'course'             => get_course($templateid),
    'fixedtargetid'      => $fixedtargetidfromget,
    'locktarget_on_load' => (bool)$fixedtargetidfromget,
]);

// Defaults setzen: bei vorhandener Auswahl (POST/GET) den Wert vormerken (ohne Lock).
if ($currenttargetid && isset($courselist[$currenttargetid])) {
    $mform->set_data((object)['targetcourseid' => $currenttargetid]);
}

// Seite konfigurieren.
$PAGE->set_url($url);
$PAGE->set_pagelayout('standard');
$PAGE->set_context($systemcontext);
$PAGE->set_title(get_string('creation_page_title', 'local_ocbsbcoursecreation'));

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('creation_page_title', 'local_ocbsbcoursecreation'));

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/local/ocbsbcoursecreation/list_courses_to_copy.php'));
} else if ($mdata = $mform->get_data()) {
    // Pflichtfelder zusammenführen.
    $mdata->templateid     = $templateid;
    $mdata->targetcourseid = (int)$mdata->targetcourseid;

    // Zusätzliche Sicherheit: Checkbox muss gesetzt sein.
    if (empty($mdata->confirmoverwrite)) {
        throw new moodle_exception('confirm_overwrite_required', 'local_ocbsbcoursecreation');
    }

    // Sicherheit: Der/die Nutzer:in muss im Zielkurs ausreichende Rechte haben.
    $targetcontext = context_course::instance($mdata->targetcourseid);
    require_capability('moodle/course:update', $targetcontext);

    // Erwarteter Feldname analog zum Kursformular: overviewfiles_filemanager.
    $mdata->hasoverview = false;
    $mdata->overviewdraftid = 0;
    if (!empty($mdata->overviewfiles_filemanager)) {
        $mdata->overviewdraftid = (int)$mdata->overviewfiles_filemanager;
        // Draft-Area prüfen, ob mind. 1 Datei hochgeladen wurde.
        $info = file_get_draft_area_info($mdata->overviewdraftid, true);
        if (!empty($info['filecount'])) {
            // Es wurde aktiv ein Bild ausgewählt.
            $mdata->hasoverview = true;
        }
    }

    // Kopiervorgang ausführen.
    $manager = new manager();
    $newcourseid = $manager->replace_course_with_template($mdata);

    redirect(new moodle_url('/course/view.php', ['id' => $newcourseid]));
} else {
    // Bei Validationsfehlern die sichtbare Seiten-URL mit der aktuellen Auswahl updaten (Persistenz).
    if ($mform->is_submitted() && $currenttargetid) {
        $PAGE->set_url(new moodle_url(
            '/local/ocbsbcoursecreation/handle_copy_form.php',
            ['templateid' => $templateid, 'targetcourseid' => $currenttargetid]
        ));
    }

    $mform->display();
}

echo $OUTPUT->footer();
