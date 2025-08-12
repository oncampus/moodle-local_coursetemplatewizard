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

// Check: Nur Trainer und Kursersteller dürfen zugreifen.
$systemcontext = context_system::instance();
if (!has_capability('moodle/course:create', $systemcontext) && !has_capability('moodle/course:update', $systemcontext)) {
    throw new required_capability_exception($systemcontext, 'moodle/course:create', 'nopermissions', '');
}

// Aus Plugin-Settings: Kategorie für Vorlagen laden.
$setcoursecategory = get_config('local_ocbsbcoursecreation', 'category');
$categories        = core_course_category::get_all(['returnhidden' => true]);
$category          = null;

foreach ($categories as $item) {
    if ($item->name === $setcoursecategory) {
        $category = $item;
        break;
    }
}

if (is_null($category)) {
    redirect(new moodle_url('/admin/search.php'), 'Selected category missing.', 1);
}

// Seiteneinstellungen.
$targetcourseid = optional_param('targetcourseid', 0, PARAM_INT);
$PAGE->set_url(new moodle_url('/local/ocbsbcoursecreation/list_courses_to_copy.php', [
    'targetcourseid' => $targetcourseid,
]));
$PAGE->set_context($systemcontext);
$PAGE->set_title(get_string('creation_page_title', 'local_ocbsbcoursecreation'));
$PAGE->set_heading(get_site()->fullname);
$PAGE->set_pagelayout('standard');

$manager = new manager();

// Alle Vorlagenkurse in der definierten Kategorie.
$courseids = $category->get_courses(['idonly' => true]);
$courses   = [];
$i         = 0;

foreach ($courseids as $courseid) {
    $course = $DB->get_record('course', ['id' => $courseid]);
    $course->fullname = format_text($course->fullname);

    // Bild.
    $img = html_writer::img(
        course_summary_exporter::get_course_image($course),
        "",
        ["width" => "100%", 'style' => "max-width: 350px;"]
    );

    // Zusammenfassung.
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
        'copy_url' => (new moodle_url(
            '/local/ocbsbcoursecreation/handle_copy_form.php',
            ['templateid' => $courseid, 'targetcourseid' => $targetcourseid]
        ))->out(false),
    ];

    $i++;
}

$boolcoursesincat = $category->has_courses();

$templatecontext = (object)[
    'courses'            => $courses,
    'courseCategoryName' => $category->name,
    'coursesInCat'       => $boolcoursesincat,
];

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_ocbsbcoursecreation/course_list_view', $templatecontext);
echo $OUTPUT->footer();
