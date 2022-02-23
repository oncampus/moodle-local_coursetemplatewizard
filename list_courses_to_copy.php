<?php

/**
 * List courses to copy
 * @package     local_oc_course_creation
 * @copyright   2021 Laurenz Schindler <Laurenz.Schindler@oncampus.de>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @var $PAGE
 * @var $OUTPUT
 */
global $CFG, $DB;
require('../../config.php');
require_once($CFG->dirroot . '/course/classes/category.php');

use local_oc_course_creation\manager;
use core_course\external\course_summary_exporter;

require_login();

$setCourseCategory = get_config('local_oc_course_creation', 'category');
$categories = core_course_category::get_all(array('returnhidden' => true));
$category = null;

foreach ($categories as $item) {
    if ($item->name === $setCourseCategory) {
        $category = $item;
    }
}
if (is_null($category)) {
    redirect(new moodle_url('/admin/search.php'), 'Selected category missing.', 1);
}

$capabilities=[
        'moodle/course:create'
];

require_all_capabilities($capabilities,context_coursecat::instance($category->id));

$PAGE->set_url(new moodle_url('/local/oc_course_creation/list_courses_to_copy.php'));
$PAGE->set_context(\context_system::instance());
$PAGE->set_title(get_string('creation_page_title', 'local_oc_course_creation'));
$PAGE->set_pagelayout('admin');
$PAGE->set_heading(get_site()->fullname);

$manager = new manager();

$courseIds = $category->get_courses(array('idonly' => true));

$context = context_coursecat::instance($category->id);

$copy_url = new moodle_url('/local/oc_course_creation/handle_copy_form.php');

$courses = array();
$i = 0;

foreach ($courseIds as $courseId) {
    $course = $DB->get_record('course', array('id' => $courseId));
    $courses[$i] = $course;
    //image
    $out = html_writer::img(course_summary_exporter::get_course_image($course), "",
            ["width" => "100%;", 'style' => "max-width: 350px;"]);
    $courses[$i]->img = $out;
    //summary
    $summary = $manager->get_course_summary((int) $courseId);
    $offset = 500; //chars until end is searched
    $end = '</p>'; //closing tag
    if (strlen($summary) > $offset && strpos($summary, $end, $offset)) {
        $result = substr($summary, 0, strlen($end) + (strpos($summary, $end, $offset)));
    } else {
        $result = $summary;
    }
    $courses[$i]->desc = $result;
    $courses[$i]->copy_url = $copy_url;

    $courses[$i]->course_url = new moodle_url('/course/view.php', array('id' => $courseId));

    $i++;
}

$boolCoursesInCat = $category->has_courses();

$templatecontext = (object) [
        'courses' => $courses,
        'courseCategoryName' => $category->name,
        'coursesInCat' => $boolCoursesInCat,
];

echo $OUTPUT->header();

echo $OUTPUT->render_from_template('local_oc_course_creation/course_list_view', $templatecontext);

echo $OUTPUT->footer();

