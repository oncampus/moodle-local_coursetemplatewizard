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
 * List courses to copy (Kursvorlagenübersicht)
 *
 * @package     local_ocbsbcoursecreation
 * @copyright   2025 Oncampus GmbH
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @var $PAGE
 * @var $OUTPUT
 */

require('../../config.php');
require_once($CFG->dirroot . '/course/classes/category.php');

use local_ocbsbcoursecreation\manager;
use core_course\external\course_summary_exporter;

require_login();

// Vorlagen-Kategorie aus Plugin-Settings robust ermitteln (ID bevorzugt, sonst Name).
$setcoursecategory = trim((string)get_config('local_ocbsbcoursecreation', 'category'));
$category = null;

if ($setcoursecategory !== '') {
    // Wenn die Einstellung numerisch ist, als ID interpretieren.
    if (ctype_digit($setcoursecategory)) {
        try {
            $category = \core_course_category::get((int)$setcoursecategory, IGNORE_MISSING, true);
        } catch (\Throwable $e) {
            $category = null;
        }
    }
    // Fallback: per Name suchen.
    if ($category === null) {
        $categories = \core_course_category::get_all(['returnhidden' => true]);
        foreach ($categories as $item) {
            if ($item->name === $setcoursecategory) {
                $category = $item;
                break;
            }
        }
    }
}

if ($category === null) {
    redirect(new moodle_url('/admin/search.php'), 'Selected template category missing or misconfigured.', 1);
}

// Zugriffsprüfung: Zielkurs- oder Kategorienkontext (NICHT Systemkontext).
$systemcontext = context_system::instance();

$targetcourseid = optional_param('targetcourseid', 0, PARAM_INT);
$targetctx = $targetcourseid ? context_course::instance($targetcourseid) : null;
$templatecatctx = context_coursecat::instance($category->id);

// Erlaubt: Nutzer darf (a) Zielkurs bearbeiten ODER (b) in der Vorlagenkategorie Kurse anlegen (falls Neuanlage genutzt wird).
$canupdate = $targetctx ? has_capability('moodle/course:update', $targetctx) : false;
$cancreate = has_capability('moodle/course:create', $templatecatctx);

if (!$canupdate && !$cancreate) {
    if ($targetctx) {
        throw new required_capability_exception($targetctx, 'moodle/course:update', 'nopermissions', '');
    } else {
        throw new required_capability_exception($templatecatctx, 'moodle/course:create', 'nopermissions', '');
    }
}

// Seiteneinstellungen.
$PAGE->set_url(new moodle_url('/local/ocbsbcoursecreation/list_courses_to_copy.php', [
    'targetcourseid' => $targetcourseid,
]));
$PAGE->set_context($systemcontext);
$PAGE->set_title(get_string('creation_page_title', 'local_ocbsbcoursecreation'));
$PAGE->set_heading(get_site()->fullname);
$PAGE->set_pagelayout('standard');

$manager = new manager();


// Vorlagenkurse laden – auch wenn verborgen (direkt aus DB).
global $DB;

$courseids = array_keys($DB->get_records('course', ['category' => $category->id], 'sortorder', 'id'));
$courses   = [];
$i         = 0;

foreach ($courseids as $courseid) {
    $course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
    $course->fullname = format_text($course->fullname);

    // Kursbild.
    $img = html_writer::img(
        course_summary_exporter::get_course_image($course),
        "",
        ["width" => "100%", 'style' => "max-width: 350px;"]
    );

    // Zusammenfassung kürzen.
    $summary = format_text($manager->get_course_summary((int)$courseid));
    $offset  = 500;
    $end     = '</p>';
    if (strlen($summary) > $offset && strpos($summary, $end, $offset)) {
        $result = substr($summary, 0, strlen($end) + (strpos($summary, $end, $offset)));
    } else {
        $result = $summary;
    }

    $courses[$i] = (object)[
        'id'          => $courseid,
        'fullname'    => $course->fullname,
        'img'         => $img,
        'desc'        => $result,
        'course_url'  => new moodle_url('/course/view.php', ['id' => $courseid]),
        'copy_url'    => (new moodle_url(
            '/local/ocbsbcoursecreation/handle_copy_form.php',
            ['templateid' => $courseid, 'targetcourseid' => $targetcourseid]
        ))->out(false),
    ];

    $i++;
}

$boolcoursesincat = !empty($courseids);

$templatecontext = (object)[
    'courses'            => $courses,
    'courseCategoryName' => $category->name,
    'coursesInCat'       => $boolcoursesincat,
];

$PAGE->requires->js_call_amd('local_ocbsbcoursecreation/imagepicker', 'init');

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_ocbsbcoursecreation/course_list_view', $templatecontext);
echo $OUTPUT->footer();
