<?php

/**
 * @package     local_oc_course_creation
 * @category    manager
 * @copyright   2021 Laurenz Schindler <Laurenz.Schindler@oncampus.de>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
global $CFG, $DB;
require('../../config.php');
require_once($CFG->dirroot . '/course/classes/category.php');

//use local_oc_course_creation\form\string_form;
use local_oc_course_creation\form\string_form;
use local_oc_course_creation\manager;

$PAGE->set_url(new moodle_url('/local/oc_course_creation/list_courses_to_copy.php'));
$PAGE->set_context(\context_system::instance());
$PAGE->set_title(get_string('creation_page_title', 'local_oc_course_creation'));
$PAGE->set_pagelayout('admin');

$manager = new manager();

$setCourseCategory = get_config('local_oc_course_creation', 'category');
$categories = core_course_category::get_all(array('returnhidden' => true));
$category = null;

foreach ($categories as $item) {
    if ($item->name == $setCourseCategory) {
        $category = $item;
    }
}
if(is_null($category)){
    redirect(new moodle_url('/admin/search.php'), 'Selected category missing.', 1);
}

$courseIds = $category->get_courses(array('idonly' => true));

$context = context_coursecat::instance($category->id);
//$PAGE->requires->js_call_amd('local_oc_course_creation/create_course_copy_modal', 'init', array($context->id));

$url = new moodle_url('/local/oc_course_creation/handle_copy_form.php');

$courses = array();
$i = 0;
foreach ($courseIds as $courseId) {
    $courses[$i++] = $DB->get_record('course', array('id' => $courseId));
}

$boolCoursesInCat = $category->has_courses();

$templatecontext = (object) [
        'courses' => $courses,
        'courseCategoryName' => $category->name,
        'coursesInCat' => $boolCoursesInCat,
        'url' => $url
];

echo $OUTPUT->header();

echo $OUTPUT->render_from_template('local_oc_course_creation/course_list_view', $templatecontext);

echo $OUTPUT->footer();

