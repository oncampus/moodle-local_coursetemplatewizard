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
 * List courses to copy (course template overview)
 *
 * @package     local_coursetemplatewizard
 * @copyright   2025 oncampus GmbH <support@oncampus.de>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @var $PAGE
 * @var $OUTPUT
 */

require('../../config.php');
require_once($CFG->dirroot . '/course/classes/category.php');

use local_coursetemplatewizard\template_utilization_manager;
use core_course\external\course_summary_exporter;

require_login();

// Determining the template category by id.
$category = null;
$raw = get_config('local_coursetemplatewizard', 'templatecoursecategoryid');
$setcoursecategory = trim((string)$raw);
if ($setcoursecategory !== '') {
    // The setting created by a category select stores a numeric category ID.
    $id = (int)$setcoursecategory;
    if ($id > 0) {
        $category = \core_course_category::get($id, IGNORE_MISSING, true);
    }
}
$targetcourseid = required_param('targetcourseid', PARAM_INT);
if ($category === null) {
    $redirecturl = new moodle_url('/course/view.php', ['id' => $targetcourseid]);
    redirect($redirecturl, 'Selected template category missing or misconfigured.', 1);
}

$templatetargetcourseexceptionsconfig = get_config('local_coursetemplatewizard', 'templatetargetcourseexceptions');
$templatetargetcourseexceptions = explode(',', $templatetargetcourseexceptionsconfig);
if (in_array($targetcourseid, $templatetargetcourseexceptions, true)) {
    return;
}

// Access check: target course or category.
$systemcontext = context_system::instance();
$targetctx = $targetcourseid ? context_course::instance($targetcourseid) : null;
$templatecatctx = context_coursecat::instance($category->id);

// The page is shown, if the user is able to
// edit the target course
// and has the capability to use templates to overwrite existing courses.
$canupdate = $targetctx ? has_capability('moodle/course:update', $targetctx) : false;
if (!$canupdate) {
    throw new required_capability_exception($targetctx, 'moodle/course:update', 'nopermissions', '');
}
if (!has_capability('local/coursetemplatewizard:use', $targetctx)) {
    throw new required_capability_exception(
        $targetctx,
        'local/coursetemplatewizard:use',
        'nopermissions',
        ''
    );
}

// Site configuration.
$PAGE->set_url(new moodle_url('/local/coursetemplatewizard/list_courses_to_copy.php', [
    'targetcourseid' => $targetcourseid,
]));
$PAGE->set_context($systemcontext);
$PAGE->set_title(get_string('creation_page_title', 'local_coursetemplatewizard'));
$PAGE->set_heading(get_site()->fullname);
$PAGE->set_pagelayout('standard');

// Reading course templates – hidden templates included.
global $DB;
$coursetemplates = $DB->get_records('course', ['category' => $category->id], 'sortorder');
$courses   = [];
foreach ($coursetemplates as $course) {
    $course->fullname = format_text($course->fullname);

    $courseimage = html_writer::img(
        course_summary_exporter::get_course_image($course),
        "",
        ["width" => "100%", 'style' => "max-width: 350px;"]
    );
    // Trimming summary for course description.
    $manager = new template_utilization_manager();
    $summary = format_text($manager->get_course_summary((int)$course->id));
    $offset  = 500;
    $end     = '</p>';
    if (strlen($summary) > $offset && strpos($summary, $end, $offset)) {
        $coursedescription = substr($summary, 0, strlen($end) + (strpos($summary, $end, $offset)));
    } else {
        $coursedescription = $summary;
    }

    $courses[] = (object)[
        'id'          => $course->id,
        'fullname'    => $course->fullname,
        'img'         => $courseimage,
        'desc'        => $coursedescription,
        'course_url'  => new moodle_url('/course/view.php', ['id' => $course->id]),
        'copy_url'    => (new moodle_url(
            '/local/coursetemplatewizard/handle_copy_form.php',
            ['templateid' => $course->id, 'targetcourseid' => $targetcourseid]
        ))->out(false),
    ];
}

$boolcoursesincat = !empty($coursetemplates);
$templatecontext = (object)[
    'courses'            => $courses,
    'courseCategoryName' => $category->name,
    'coursesInCat'       => $boolcoursesincat,
    'modalimage'         => $OUTPUT->image_url('tiny', 'local_coursetemplatewizard')->out(),
];

$PAGE->requires->js_call_amd('local_coursetemplatewizard/imagepicker', 'init');

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_coursetemplatewizard/course_list_view', $templatecontext);
echo $OUTPUT->footer();
